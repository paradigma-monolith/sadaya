import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { createHash } from 'node:crypto';
import { parse, compileScript, compileTemplate } from '@vue/compiler-sfc';

const ROOT_DIR = path.resolve(import.meta.dirname, '..');
const JS_DIR = path.join(ROOT_DIR, 'resources', 'js');

const collectVueFiles = (dir) => {
  const results = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      results.push(...collectVueFiles(full));
    } else if (entry.isFile() && entry.name.endsWith('.vue')) {
      results.push(full);
    }
  }
  return results.sort();
};

const NODE_TEXT = 2;
const NODE_ELEMENT = 1;
const NODE_INTERPOLATION = 5;
const NODE_IF = 9;
const NODE_FOR = 11;
const NODE_IF_BRANCH = 10;
const DIR_BIND = 7;

const hasAccessibleLabelProp = (props = []) =>
  props.some((prop) => {
    if (prop.type === 6 /* ATTRIBUTE */) {
      return (prop.name === 'aria-label' || prop.name === 'title') && Boolean(prop.value?.content?.trim());
    }
    if (prop.type === DIR_BIND && prop.name === 'bind') {
      const argName = prop.arg?.content;
      return argName === 'aria-label' || argName === 'title';
    }
    return false;
  });

const hasTextContent = (children = []) => {
  for (const node of children) {
    if (!node) continue;
    if (node.type === NODE_TEXT && node.content.trim().length > 0) return true;
    if (node.type === NODE_INTERPOLATION) return true;
    if (node.type === NODE_ELEMENT && node.tag === 'span') {
      // Check visible or sr-only span text
      if (hasTextContent(node.children || [])) return true;
    }
    if (node.type === NODE_ELEMENT && node.children?.length) {
      if (hasTextContent(node.children)) return true;
    }
    if (node.type === NODE_IF && node.branches?.some((b) => hasTextContent(b.children || []))) {
      return true;
    }
    if (node.type === NODE_IF_BRANCH && hasTextContent(node.children || [])) return true;
    if (node.type === NODE_FOR && hasTextContent(node.children || [])) return true;
  }
  return false;
};

const collectUnlabelledButtons = (nodes = [], issues = []) => {
  for (const node of nodes) {
    if (!node) continue;
    if (node.type === NODE_ELEMENT && node.tag === 'button') {
      if (!hasAccessibleLabelProp(node.props) && !hasTextContent(node.children || [])) {
        issues.push({
          line: node.loc?.start?.line ?? 0,
          snippet: (node.loc?.source || '').split('\n')[0].trim().slice(0, 120),
        });
      }
    }
    if (node.children?.length) collectUnlabelledButtons(node.children, issues);
    if (node.branches?.length) collectUnlabelledButtons(node.branches, issues);
  }
  return issues;
};

const files = collectVueFiles(JS_DIR);
const compileErrors = [];
const a11yWarnings = [];

for (const file of files) {
  const rel = path.relative(ROOT_DIR, file).replace(/\\/g, '/');
  const source = fs.readFileSync(file, 'utf8');
  const id = createHash('sha256').update(rel).digest('hex').slice(0, 8);

  const { descriptor, errors } = parse(source, { filename: rel });
  if (errors.length > 0) {
    for (const err of errors) {
      compileErrors.push(`${rel}: parse error: ${err.message}`);
    }
    continue;
  }

  let compiledScript = null;
  if (descriptor.script || descriptor.scriptSetup) {
    try {
      compiledScript = compileScript(descriptor, { id, inlineTemplate: false });
    } catch (err) {
      compileErrors.push(`${rel}: script error: ${err.message}`);
    }
  }

  if (descriptor.template) {
    const templateResult = compileTemplate({
      source: descriptor.template.content,
      filename: rel,
      id,
      compilerOptions: {
        bindingMetadata: compiledScript?.bindings,
      },
    });
    if (templateResult.errors.length > 0) {
      for (const err of templateResult.errors) {
        const msg = typeof err === 'string' ? err : err.message;
        compileErrors.push(`${rel}: template error: ${msg}`);
      }
    }
    const rawAst = descriptor.template.ast;
    if (rawAst) {
      const btnIssues = collectUnlabelledButtons(rawAst.children || []);
      for (const issue of btnIssues) {
        a11yWarnings.push(`${rel}:${issue.line}: button tanpa teks wajib memiliki aria-label atau title (${issue.snippet})`);
      }
    }
  }
}

console.log(`Checked ${files.length} Vue SFC files.`);

if (a11yWarnings.length > 0) {
  console.error(`\n[A11Y] Found ${a11yWarnings.length} unlabelled icon-only button(s):`);
  for (const w of a11yWarnings) console.error(`  - ${w}`);
}

if (compileErrors.length > 0) {
  console.error(`\n[SFC] Found ${compileErrors.length} compile error(s):`);
  for (const e of compileErrors) console.error(`  - ${e}`);
}

if (compileErrors.length > 0 || a11yWarnings.length > 0) {
  process.exit(1);
}

console.log('All Vue SFCs parsed, compiled, and passed icon-button a11y checks.');
