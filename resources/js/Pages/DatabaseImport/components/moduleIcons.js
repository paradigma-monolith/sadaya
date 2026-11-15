import {
  Database,
  Ship,
  Plane,
  Anchor,
  PackageCheck,
  Pill,
  Users,
  Utensils,
  Droplet,
  Building,
  Bug,
} from 'lucide-vue-next';

export function getModuleIcon(iconName) {
  switch (iconName) {
    case 'Ship': return Ship;
    case 'Plane': return Plane;
    case 'Anchor': return Anchor;
    case 'PackageCheck': return PackageCheck;
    case 'Pill': return Pill;
    case 'Users': return Users;
    case 'Utensils': return Utensils;
    case 'Droplet': return Droplet;
    case 'Building': return Building;
    case 'Bug': return Bug;
    default: return Database;
  }
}

export function formatImportNumber(val) {
  if (val === undefined || val === null) return '0';
  return Number(val).toLocaleString('id-ID');
}
