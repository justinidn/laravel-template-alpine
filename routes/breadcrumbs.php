<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard'));
});

Breadcrumbs::for('profile.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Profile', route('profile.edit'));
});

Breadcrumbs::for('master-menus.index', function (BreadcrumbTrail $trail) {
    $trail->push('Master Menus', route('master-menus.index'));
});

Breadcrumbs::for('master-menus.create', function (BreadcrumbTrail $trail) {
    $trail->parent('master-menus.index');
    $trail->push('Create Menu', route('master-menus.create'));
});

Breadcrumbs::for('master-menus.show', function (BreadcrumbTrail $trail, $masterMenu) {
    $trail->parent('master-menus.index');
    $trail->push('Detail Menu', route('master-menus.show', $masterMenu));
});

Breadcrumbs::for('master-menus.edit', function (BreadcrumbTrail $trail, $masterMenu) {
    $trail->parent('master-menus.index');
    $trail->push('Edit Menu', route('master-menus.edit', $masterMenu));
});

Breadcrumbs::for('departments.index', function (BreadcrumbTrail $trail) {
    $trail->push('Departments', route('departments.index'));
});
