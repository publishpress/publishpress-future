import { createRoot } from '@wordpress/element';

import BackupPanel from './components/backup-panel';

import './css/general.css';

const container = document.getElementById("backup-panel");

if (container) {
    const component = (<BackupPanel />);

    createRoot(container).render(component);
}
