

// 2. Import Bootstrap JS & Dependencies
import './bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// 3. Import jQuery & DataTables
import $ from 'jquery';
window.$ = window.jQuery = $;

import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
window.DataTable = DataTable;

// 4. Import Helper & Alpine JS
import './datatables-helper'; // Memuat helper DataTable secara global

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();