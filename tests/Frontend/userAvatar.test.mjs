import test from 'node:test';
import assert from 'node:assert/strict';
import { getUserInitials } from '../../resources/js/utils/userAvatar.js';

test('getUserInitials formats single, multi-word, and empty names consistently', () => {
  assert.equal(getUserInitials('Demo Admin'), 'DA');
  assert.equal(getUserInitials('Budi Santoso Pratama'), 'BP');
  assert.equal(getUserInitials('Administrator'), 'AD');
  assert.equal(getUserInitials(''), 'AD');
  assert.equal(getUserInitials(null, 'BK'), 'BK');
});
