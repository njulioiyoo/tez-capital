-- Updated menu data for Tez Capital
-- Clear existing data
DELETE FROM menu_items;

-- Insert menu items with new structure
INSERT INTO menu_items (id, title, href, icon, position, parent_id, badge, disabled, is_separator, is_active, created_at, updated_at) VALUES 
-- Dashboard
(1, 'Dashboard', '/dashboard', 'LayoutGrid', 1, NULL, NULL, false, false, true, NOW(), NOW()),

-- System Management
(2, 'System Management', NULL, 'Settings', 2, NULL, NULL, false, false, true, NOW(), NOW()),

-- Users under System Management
(3, 'Users', '/users', 'Users', 1, 2, NULL, false, false, true, NOW(), NOW()),

-- Roles & Permissions under System Management  
(4, 'Roles & Permissions', '/users/roles', 'Shield', 2, 2, NULL, false, false, true, NOW(), NOW()),

-- Menus under System Management
(5, 'Menus', '/menu-manager', 'LayoutGrid', 3, 2, NULL, false, false, true, NOW(), NOW()),

-- Audit Log under System Management
(6, 'Audit Log', '/audit-log', 'FileText', 4, 2, NULL, false, false, true, NOW(), NOW()),

-- System Configuration under System Management
(7, 'System Configuration', '/system/config', 'Settings', 5, 2, NULL, false, false, true, NOW(), NOW()),

-- Reports
(8, 'Reports', NULL, 'BarChart3', 3, NULL, NULL, false, false, true, NOW(), NOW()),

-- Analytics under Reports
(9, 'Analytics', '/reports/analytics', 'BarChart3', 1, 8, NULL, false, false, true, NOW(), NOW()),

-- Financial Reports under Reports
(10, 'Financial Reports', '/reports/financial', 'CreditCard', 2, 8, NULL, false, false, true, NOW(), NOW());

-- Reset sequence
SELECT setval('menu_items_id_seq', (SELECT MAX(id) FROM menu_items));