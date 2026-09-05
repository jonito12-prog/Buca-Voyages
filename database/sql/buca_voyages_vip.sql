IF DB_ID(N'buca_voyages_vip') IS NULL
BEGIN
    CREATE DATABASE [buca_voyages_vip];
END
GO

USE [buca_voyages_vip];
GO

IF OBJECT_ID(N'dbo.audit_logs', N'U') IS NOT NULL DROP TABLE dbo.audit_logs;
IF OBJECT_ID(N'dbo.notifications', N'U') IS NOT NULL DROP TABLE dbo.notifications;
IF OBJECT_ID(N'dbo.trip_consumptions', N'U') IS NOT NULL DROP TABLE dbo.trip_consumptions;
IF OBJECT_ID(N'dbo.payments', N'U') IS NOT NULL DROP TABLE dbo.payments;
IF OBJECT_ID(N'dbo.card_subscriptions', N'U') IS NOT NULL DROP TABLE dbo.card_subscriptions;
IF OBJECT_ID(N'dbo.vip_cards', N'U') IS NOT NULL DROP TABLE dbo.vip_cards;
IF OBJECT_ID(N'dbo.vip_clients', N'U') IS NOT NULL DROP TABLE dbo.vip_clients;
IF OBJECT_ID(N'dbo.packages', N'U') IS NOT NULL DROP TABLE dbo.packages;
IF OBJECT_ID(N'dbo.travel_routes', N'U') IS NOT NULL DROP TABLE dbo.travel_routes;
IF OBJECT_ID(N'dbo.permission_role', N'U') IS NOT NULL DROP TABLE dbo.permission_role;
IF OBJECT_ID(N'dbo.permissions', N'U') IS NOT NULL DROP TABLE dbo.permissions;
IF OBJECT_ID(N'dbo.sessions', N'U') IS NOT NULL DROP TABLE dbo.sessions;
IF OBJECT_ID(N'dbo.cache_locks', N'U') IS NOT NULL DROP TABLE dbo.cache_locks;
IF OBJECT_ID(N'dbo.cache', N'U') IS NOT NULL DROP TABLE dbo.cache;
IF OBJECT_ID(N'dbo.failed_jobs', N'U') IS NOT NULL DROP TABLE dbo.failed_jobs;
IF OBJECT_ID(N'dbo.jobs', N'U') IS NOT NULL DROP TABLE dbo.jobs;
IF OBJECT_ID(N'dbo.job_batches', N'U') IS NOT NULL DROP TABLE dbo.job_batches;
IF OBJECT_ID(N'dbo.users', N'U') IS NOT NULL DROP TABLE dbo.users;
IF OBJECT_ID(N'dbo.agencies', N'U') IS NOT NULL DROP TABLE dbo.agencies;
IF OBJECT_ID(N'dbo.roles', N'U') IS NOT NULL DROP TABLE dbo.roles;
IF OBJECT_ID(N'dbo.password_reset_tokens', N'U') IS NOT NULL DROP TABLE dbo.password_reset_tokens;
IF OBJECT_ID(N'dbo.migrations', N'U') IS NOT NULL DROP TABLE dbo.migrations;
GO

CREATE TABLE dbo.migrations (id INT IDENTITY(1,1) NOT NULL PRIMARY KEY, migration NVARCHAR(255) NOT NULL, batch INT NOT NULL);
CREATE TABLE dbo.roles (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, name NVARCHAR(255) NOT NULL, slug NVARCHAR(255) NOT NULL UNIQUE, description NVARCHAR(255) NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL);
CREATE TABLE dbo.agencies (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, name NVARCHAR(255) NOT NULL, city NVARCHAR(255) NOT NULL, district NVARCHAR(255) NULL, address NVARCHAR(255) NULL, phone NVARCHAR(255) NULL, status NVARCHAR(255) NOT NULL DEFAULT 'active', created_at DATETIME2 NULL, updated_at DATETIME2 NULL);
CREATE INDEX agencies_city_status_index ON dbo.agencies(city, status);

CREATE TABLE dbo.users (
    id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    email NVARCHAR(255) NOT NULL UNIQUE,
    phone NVARCHAR(255) NULL,
    email_verified_at DATETIME2 NULL,
    password NVARCHAR(255) NOT NULL,
    role_id BIGINT NULL,
    agency_id BIGINT NULL,
    status NVARCHAR(255) NOT NULL DEFAULT 'active',
    last_login_at DATETIME2 NULL,
    remember_token NVARCHAR(100) NULL,
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL,
    CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES dbo.roles(id) ON DELETE SET NULL,
    CONSTRAINT users_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES dbo.agencies(id) ON DELETE SET NULL
);

CREATE TABLE dbo.password_reset_tokens (email NVARCHAR(255) NOT NULL PRIMARY KEY, token NVARCHAR(255) NOT NULL, created_at DATETIME2 NULL);
CREATE TABLE dbo.sessions (id NVARCHAR(255) NOT NULL PRIMARY KEY, user_id BIGINT NULL, ip_address NVARCHAR(45) NULL, user_agent NVARCHAR(MAX) NULL, payload NVARCHAR(MAX) NOT NULL, last_activity INT NOT NULL);
CREATE INDEX sessions_user_id_index ON dbo.sessions(user_id);
CREATE INDEX sessions_last_activity_index ON dbo.sessions(last_activity);
CREATE TABLE dbo.cache ([key] NVARCHAR(255) NOT NULL PRIMARY KEY, value NVARCHAR(MAX) NOT NULL, expiration INT NOT NULL);
CREATE TABLE dbo.cache_locks ([key] NVARCHAR(255) NOT NULL PRIMARY KEY, owner NVARCHAR(255) NOT NULL, expiration INT NOT NULL);
CREATE TABLE dbo.jobs (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, queue NVARCHAR(255) NOT NULL, payload NVARCHAR(MAX) NOT NULL, attempts TINYINT NOT NULL, reserved_at INT NULL, available_at INT NOT NULL, created_at INT NOT NULL);
CREATE INDEX jobs_queue_index ON dbo.jobs(queue);
CREATE TABLE dbo.job_batches (id NVARCHAR(255) NOT NULL PRIMARY KEY, name NVARCHAR(255) NOT NULL, total_jobs INT NOT NULL, pending_jobs INT NOT NULL, failed_jobs INT NOT NULL, failed_job_ids NVARCHAR(MAX) NOT NULL, options NVARCHAR(MAX) NULL, cancelled_at INT NULL, created_at INT NOT NULL, finished_at INT NULL);
CREATE TABLE dbo.failed_jobs (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, uuid NVARCHAR(255) NOT NULL UNIQUE, connection NVARCHAR(MAX) NOT NULL, queue NVARCHAR(MAX) NOT NULL, payload NVARCHAR(MAX) NOT NULL, exception NVARCHAR(MAX) NOT NULL, failed_at DATETIME2 NOT NULL DEFAULT SYSDATETIME());

CREATE TABLE dbo.permissions (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, name NVARCHAR(255) NOT NULL, slug NVARCHAR(255) NOT NULL UNIQUE, module NVARCHAR(255) NOT NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL);
CREATE TABLE dbo.permission_role (role_id BIGINT NOT NULL, permission_id BIGINT NOT NULL, CONSTRAINT permission_role_primary PRIMARY KEY (role_id, permission_id), CONSTRAINT permission_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES dbo.roles(id) ON DELETE CASCADE, CONSTRAINT permission_role_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES dbo.permissions(id) ON DELETE CASCADE);

CREATE TABLE dbo.travel_routes (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, departure_city NVARCHAR(255) NOT NULL, arrival_city NVARCHAR(255) NOT NULL, distance_km INT NULL, estimated_duration NVARCHAR(255) NULL, status NVARCHAR(255) NOT NULL DEFAULT 'active', created_at DATETIME2 NULL, updated_at DATETIME2 NULL);
CREATE INDEX travel_routes_departure_city_arrival_city_status_index ON dbo.travel_routes(departure_city, arrival_city, status);

CREATE TABLE dbo.packages (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, name NVARCHAR(255) NOT NULL, period_type NVARCHAR(255) NOT NULL, trip_count INT NOT NULL, validity_days INT NOT NULL, price DECIMAL(12,2) NOT NULL, travel_route_id BIGINT NULL, class_type NVARCHAR(255) NOT NULL DEFAULT 'vip', status NVARCHAR(255) NOT NULL DEFAULT 'active', created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT packages_travel_route_id_foreign FOREIGN KEY (travel_route_id) REFERENCES dbo.travel_routes(id) ON DELETE SET NULL);
CREATE INDEX packages_period_type_class_type_status_index ON dbo.packages(period_type, class_type, status);

CREATE TABLE dbo.vip_clients (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, first_name NVARCHAR(255) NOT NULL, last_name NVARCHAR(255) NOT NULL, gender NVARCHAR(255) NULL, birth_date DATE NULL, phone NVARCHAR(255) NOT NULL, email NVARCHAR(255) NULL, address NVARCHAR(255) NULL, identity_type NVARCHAR(255) NULL, identity_number NVARCHAR(255) NULL, status NVARCHAR(255) NOT NULL DEFAULT 'active', created_by BIGINT NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT vip_clients_created_by_foreign FOREIGN KEY (created_by) REFERENCES dbo.users(id) ON DELETE SET NULL);
CREATE INDEX vip_clients_phone_status_index ON dbo.vip_clients(phone, status);
CREATE INDEX vip_clients_last_name_first_name_index ON dbo.vip_clients(last_name, first_name);

CREATE TABLE dbo.vip_cards (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, vip_client_id BIGINT NOT NULL, card_number NVARCHAR(255) NOT NULL UNIQUE, qr_code NVARCHAR(255) NULL, card_type NVARCHAR(255) NOT NULL DEFAULT 'vip', status NVARCHAR(255) NOT NULL DEFAULT 'active', issued_at DATETIME2 NULL, expires_at DATETIME2 NULL, suspended_at DATETIME2 NULL, suspension_reason NVARCHAR(255) NULL, created_by BIGINT NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT vip_cards_vip_client_id_foreign FOREIGN KEY (vip_client_id) REFERENCES dbo.vip_clients(id) ON DELETE CASCADE, CONSTRAINT vip_cards_created_by_foreign FOREIGN KEY (created_by) REFERENCES dbo.users(id) ON DELETE SET NULL);
CREATE INDEX vip_cards_vip_client_id_status_index ON dbo.vip_cards(vip_client_id, status);

CREATE TABLE dbo.card_subscriptions (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, vip_card_id BIGINT NOT NULL, package_id BIGINT NOT NULL, starts_at DATETIME2 NOT NULL, expires_at DATETIME2 NOT NULL, trips_total INT NOT NULL, trips_remaining INT NOT NULL, unit_price DECIMAL(12,2) NOT NULL DEFAULT 0, total_amount DECIMAL(12,2) NOT NULL DEFAULT 0, status NVARCHAR(255) NOT NULL DEFAULT 'active', renewed_from_id BIGINT NULL, created_by BIGINT NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT card_subscriptions_vip_card_id_foreign FOREIGN KEY (vip_card_id) REFERENCES dbo.vip_cards(id) ON DELETE CASCADE, CONSTRAINT card_subscriptions_package_id_foreign FOREIGN KEY (package_id) REFERENCES dbo.packages(id), CONSTRAINT card_subscriptions_renewed_from_id_foreign FOREIGN KEY (renewed_from_id) REFERENCES dbo.card_subscriptions(id) ON DELETE NO ACTION, CONSTRAINT card_subscriptions_created_by_foreign FOREIGN KEY (created_by) REFERENCES dbo.users(id) ON DELETE SET NULL, CONSTRAINT card_subscriptions_trips_remaining_check CHECK (trips_remaining >= 0));
CREATE INDEX card_subscriptions_vip_card_id_status_expires_at_index ON dbo.card_subscriptions(vip_card_id, status, expires_at);

CREATE TABLE dbo.payments (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, card_subscription_id BIGINT NOT NULL, vip_client_id BIGINT NOT NULL, amount DECIMAL(12,2) NOT NULL, payment_method NVARCHAR(255) NOT NULL, payment_status NVARCHAR(255) NOT NULL DEFAULT 'pending', transaction_reference NVARCHAR(255) NULL UNIQUE, paid_at DATETIME2 NULL, received_by BIGINT NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT payments_card_subscription_id_foreign FOREIGN KEY (card_subscription_id) REFERENCES dbo.card_subscriptions(id) ON DELETE NO ACTION, CONSTRAINT payments_vip_client_id_foreign FOREIGN KEY (vip_client_id) REFERENCES dbo.vip_clients(id) ON DELETE NO ACTION, CONSTRAINT payments_received_by_foreign FOREIGN KEY (received_by) REFERENCES dbo.users(id) ON DELETE SET NULL);
CREATE INDEX payments_payment_status_payment_method_index ON dbo.payments(payment_status, payment_method);

CREATE TABLE dbo.trip_consumptions (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, card_subscription_id BIGINT NOT NULL, vip_card_id BIGINT NOT NULL, vip_client_id BIGINT NOT NULL, travel_route_id BIGINT NOT NULL, departure_agency_id BIGINT NOT NULL, arrival_agency_id BIGINT NULL, travel_date DATE NOT NULL, consumed_at DATETIME2 NOT NULL, consumed_by BIGINT NULL, trips_debited INT NOT NULL DEFAULT 1, reference NVARCHAR(255) NOT NULL UNIQUE, notes NVARCHAR(MAX) NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT trip_consumptions_card_subscription_id_foreign FOREIGN KEY (card_subscription_id) REFERENCES dbo.card_subscriptions(id) ON DELETE NO ACTION, CONSTRAINT trip_consumptions_vip_card_id_foreign FOREIGN KEY (vip_card_id) REFERENCES dbo.vip_cards(id) ON DELETE NO ACTION, CONSTRAINT trip_consumptions_vip_client_id_foreign FOREIGN KEY (vip_client_id) REFERENCES dbo.vip_clients(id) ON DELETE NO ACTION, CONSTRAINT trip_consumptions_travel_route_id_foreign FOREIGN KEY (travel_route_id) REFERENCES dbo.travel_routes(id), CONSTRAINT trip_consumptions_departure_agency_id_foreign FOREIGN KEY (departure_agency_id) REFERENCES dbo.agencies(id), CONSTRAINT trip_consumptions_arrival_agency_id_foreign FOREIGN KEY (arrival_agency_id) REFERENCES dbo.agencies(id) ON DELETE SET NULL, CONSTRAINT trip_consumptions_trips_debited_check CHECK (trips_debited > 0));
CREATE INDEX trip_consumptions_vip_card_id_travel_date_index ON dbo.trip_consumptions(vip_card_id, travel_date);
CREATE INDEX trip_consumptions_card_subscription_id_consumed_at_index ON dbo.trip_consumptions(card_subscription_id, consumed_at);

CREATE TABLE dbo.notifications (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, user_id BIGINT NULL, vip_client_id BIGINT NULL, type NVARCHAR(255) NOT NULL, channel NVARCHAR(255) NOT NULL, subject NVARCHAR(255) NULL, message NVARCHAR(MAX) NOT NULL, status NVARCHAR(255) NOT NULL DEFAULT 'pending', sent_at DATETIME2 NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE SET NULL, CONSTRAINT notifications_vip_client_id_foreign FOREIGN KEY (vip_client_id) REFERENCES dbo.vip_clients(id) ON DELETE SET NULL);
CREATE INDEX notifications_type_status_index ON dbo.notifications(type, status);

CREATE TABLE dbo.audit_logs (id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY, user_id BIGINT NULL, action NVARCHAR(255) NOT NULL, module NVARCHAR(255) NOT NULL, entity_type NVARCHAR(255) NOT NULL, entity_id BIGINT NULL, old_values NVARCHAR(MAX) NULL, new_values NVARCHAR(MAX) NULL, ip_address NVARCHAR(45) NULL, user_agent NVARCHAR(MAX) NULL, created_at DATETIME2 NULL, updated_at DATETIME2 NULL, CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE SET NULL);
CREATE INDEX audit_logs_module_action_index ON dbo.audit_logs(module, action);
CREATE INDEX audit_logs_entity_type_entity_id_index ON dbo.audit_logs(entity_type, entity_id);
GO

DECLARE @now DATETIME2 = SYSDATETIME();
DECLARE @password NVARCHAR(255) = N'$2y$12$D.IzbUnHNqOg8Rp66vrZ.eRdpSuUw6cNTiw7cyNc6zhcRQLXPX8Ru';

INSERT INTO dbo.migrations (migration, batch) VALUES
(N'0001_01_01_000000_create_users_table', 1),(N'0001_01_01_000001_create_cache_table', 1),(N'0001_01_01_000002_create_jobs_table', 1),(N'2026_05_25_000001_create_security_reference_tables', 1),(N'2026_05_25_000002_create_travel_reference_tables', 1),(N'2026_05_25_000003_create_vip_customer_tables', 1),(N'2026_05_25_000004_create_vip_operation_tables', 1);

INSERT INTO dbo.roles (name, slug, description, created_at, updated_at) VALUES
(N'Administrateur general', N'admin', N'Acces complet a la plateforme Buca Voyages VIP.', @now, @now),(N'Agent agence', N'agent', N'Gere les clients, cartes, forfaits, voyages et paiements en agence.', @now, @now),(N'Client VIP', N'client', N'Consulte son solde et son historique.', @now, @now);

INSERT INTO dbo.permissions (name, slug, module, created_at, updated_at) VALUES
(N'Voir le tableau de bord', N'dashboard.view', N'Tableau de bord', @now, @now),(N'Gerer les utilisateurs', N'users.manage', N'Securite', @now, @now),(N'Gerer les roles', N'roles.manage', N'Securite', @now, @now),(N'Gerer les clients VIP', N'vip_clients.manage', N'Clients VIP', @now, @now),(N'Gerer les cartes VIP', N'vip_cards.manage', N'Cartes VIP', @now, @now),(N'Suspendre une carte VIP', N'vip_cards.suspend', N'Cartes VIP', @now, @now),(N'Gerer les forfaits', N'packages.manage', N'Forfaits', @now, @now),(N'Consommer un voyage', N'trips.consume', N'Voyages', @now, @now),(N'Voir les paiements', N'payments.view', N'Paiements', @now, @now),(N'Gerer les paiements', N'payments.manage', N'Paiements', @now, @now),(N'Voir les audits', N'audit.view', N'Audit', @now, @now);

INSERT INTO dbo.permission_role (role_id, permission_id) SELECT r.id, p.id FROM dbo.roles r CROSS JOIN dbo.permissions p WHERE r.slug = N'admin';
INSERT INTO dbo.permission_role (role_id, permission_id) SELECT r.id, p.id FROM dbo.roles r JOIN dbo.permissions p ON p.slug IN (N'dashboard.view', N'vip_clients.manage', N'vip_cards.manage', N'vip_cards.suspend', N'packages.manage', N'trips.consume', N'payments.view', N'payments.manage') WHERE r.slug = N'agent';
INSERT INTO dbo.permission_role (role_id, permission_id) SELECT r.id, p.id FROM dbo.roles r JOIN dbo.permissions p ON p.slug = N'dashboard.view' WHERE r.slug = N'client';

INSERT INTO dbo.agencies (name, city, district, address, phone, status, created_at, updated_at) VALUES
(N'Agence Mvan', N'Yaounde', N'Mvan', N'Mvan, Yaounde', N'+237 690 000 001', N'active', @now, @now),(N'Agence Mboppi', N'Douala', N'Mboppi', N'Mboppi, Douala', N'+237 690 000 002', N'active', @now, @now),(N'Agence Yassa', N'Douala', N'Yassa', N'Yassa, Douala', N'+237 690 000 003', N'active', @now, @now);

INSERT INTO dbo.users (name, email, phone, password, role_id, agency_id, status, created_at, updated_at) VALUES
(N'Admin Buca VIP', N'admin@bucavoyages.test', N'+237 699 100 000', @password, 1, 1, N'active', @now, @now),(N'Agent Mboppi', N'agent@bucavoyages.test', N'+237 699 100 002', @password, 2, 2, N'active', @now, @now);

INSERT INTO dbo.travel_routes (departure_city, arrival_city, distance_km, estimated_duration, status, created_at, updated_at) VALUES
(N'Yaounde', N'Douala', 245, N'4h30', N'active', @now, @now),(N'Douala', N'Yaounde', 245, N'4h30', N'active', @now, @now),(N'Yaounde', N'Ebolowa', 168, N'3h00', N'active', @now, @now),(N'Yaounde', N'Sangmelima', 172, N'3h15', N'active', @now, @now),(N'Douala', N'Pointe-Noire', 760, N'12h00', N'active', @now, @now);

INSERT INTO dbo.packages (name, period_type, trip_count, validity_days, price, travel_route_id, class_type, status, created_at, updated_at) VALUES
(N'Hebdomadaire Classique', N'weekly', 4, 7, 28000, NULL, N'classique', N'active', @now, @now),(N'Mensuel VIP', N'monthly', 10, 30, 90000, 1, N'vip', N'active', @now, @now),(N'Mensuel VIP Plus', N'monthly', 20, 30, 170000, NULL, N'vip', N'active', @now, @now),(N'Annuel VIP', N'annual', 120, 365, 950000, NULL, N'vip', N'active', @now, @now),(N'Personnalise Entreprise', N'custom', 50, 90, 420000, NULL, N'mixed', N'active', @now, @now);

INSERT INTO dbo.vip_clients (first_name, last_name, phone, email, address, status, created_by, created_at, updated_at) VALUES
(N'Clarisse', N'Mvondo', N'+237 670 111 001', N'clarisse.mvondo@example.test', N'Mvan, Yaounde', N'active', 2, @now, @now),(N'Patrick', N'Ngono', N'+237 670 111 002', N'patrick.ngono@example.test', N'Bonamoussadi, Douala', N'active', 2, @now, @now),(N'Ariane', N'Biloa', N'+237 670 111 003', N'ariane.biloa@example.test', N'Ebolowa', N'active', 2, @now, @now),(N'Michel', N'Essomba', N'+237 670 111 004', N'michel.essomba@example.test', N'Yassa, Douala', N'active', 2, @now, @now),(N'Nadia', N'Tchinda', N'+237 670 111 005', N'nadia.tchinda@example.test', N'Mboppi, Douala', N'active', 2, @now, @now);

INSERT INTO dbo.vip_cards (vip_client_id, card_number, card_type, status, issued_at, expires_at, suspended_at, suspension_reason, created_by, created_at, updated_at) VALUES
(1, N'BUCA-VIP-0001', N'vip', N'active', DATEADD(day, -10, @now), DATEADD(year, 1, @now), NULL, NULL, 2, @now, @now),(2, N'BUCA-VIP-0002', N'vip', N'active', DATEADD(day, -10, @now), DATEADD(year, 1, @now), NULL, NULL, 2, @now, @now),(3, N'BUCA-VIP-0003', N'vip', N'expired', DATEADD(day, -45, @now), DATEADD(day, -15, @now), NULL, NULL, 2, @now, @now),(4, N'BUCA-VIP-0004', N'vip', N'suspended', DATEADD(day, -10, @now), DATEADD(year, 1, @now), DATEADD(day, -1, @now), N'Verification administrative en cours', 2, @now, @now),(5, N'BUCA-VIP-0005', N'vip', N'active', DATEADD(day, -10, @now), DATEADD(day, 80, @now), NULL, NULL, 2, @now, @now);

INSERT INTO dbo.card_subscriptions (vip_card_id, package_id, starts_at, expires_at, trips_total, trips_remaining, unit_price, total_amount, status, created_by, created_at, updated_at) VALUES
(1, 2, DATEADD(day, -10, @now), DATEADD(day, 20, @now), 10, 8, 90000, 90000, N'active', 2, @now, @now),(2, 1, DATEADD(day, -5, @now), DATEADD(day, 2, @now), 4, 1, 28000, 28000, N'active', 2, @now, @now),(3, 3, DATEADD(day, -45, @now), DATEADD(day, -15, @now), 20, 3, 170000, 170000, N'expired', 2, @now, @now),(4, 4, DATEADD(day, -30, @now), DATEADD(day, 335, @now), 120, 112, 950000, 950000, N'active', 2, @now, @now),(5, 5, DATEADD(day, -1, @now), DATEADD(day, 89, @now), 50, 50, 420000, 420000, N'pending_payment', 2, @now, @now);

INSERT INTO dbo.payments (card_subscription_id, vip_client_id, amount, payment_method, payment_status, transaction_reference, paid_at, received_by, created_at, updated_at) VALUES
(1, 1, 90000, N'cash', N'paid', N'PAY-BUCA-0001', DATEADD(day, -9, @now), 2, @now, @now),(2, 2, 28000, N'cash', N'paid', N'PAY-BUCA-0002', DATEADD(day, -4, @now), 2, @now, @now),(3, 3, 170000, N'cash', N'paid', N'PAY-BUCA-0003', DATEADD(day, -44, @now), 2, @now, @now),(4, 4, 950000, N'cash', N'paid', N'PAY-BUCA-0004', DATEADD(day, -29, @now), 2, @now, @now),(5, 5, 420000, N'mobile_money', N'pending', N'PAY-BUCA-0005', NULL, 2, @now, @now);

INSERT INTO dbo.trip_consumptions (card_subscription_id, vip_card_id, vip_client_id, travel_route_id, departure_agency_id, arrival_agency_id, travel_date, consumed_at, consumed_by, trips_debited, reference, notes, created_at, updated_at) VALUES
(1, 1, 1, 1, 1, 2, DATEADD(day, -8, @now), DATEADD(day, -8, @now), 2, 1, N'BUCA-VIP-0001-TRIP-001', N'Voyage de demonstration', @now, @now),(1, 1, 1, 1, 1, 2, DATEADD(day, -6, @now), DATEADD(day, -6, @now), 2, 1, N'BUCA-VIP-0001-TRIP-002', N'Voyage de demonstration', @now, @now),(2, 2, 2, 1, 1, 2, DATEADD(day, -4, @now), DATEADD(day, -4, @now), 2, 1, N'BUCA-VIP-0002-TRIP-001', N'Voyage de demonstration', @now, @now),(2, 2, 2, 1, 1, 2, DATEADD(day, -3, @now), DATEADD(day, -3, @now), 2, 1, N'BUCA-VIP-0002-TRIP-002', N'Voyage de demonstration', @now, @now),(2, 2, 2, 1, 1, 2, DATEADD(day, -2, @now), DATEADD(day, -2, @now), 2, 1, N'BUCA-VIP-0002-TRIP-003', N'Voyage de demonstration', @now, @now),(4, 4, 4, 2, 2, 1, DATEADD(day, -7, @now), DATEADD(day, -7, @now), 2, 1, N'BUCA-VIP-0004-TRIP-001', N'Voyage avant suspension', @now, @now);

INSERT INTO dbo.notifications (vip_client_id, type, channel, subject, message, status, sent_at, created_at, updated_at) VALUES
(2, N'low_balance', N'app', N'Solde faible', N'Votre carte VIP approche de la fin du forfait.', N'sent', @now, @now, @now),(5, N'payment_pending', N'app', N'Paiement en attente', N'Le forfait sera actif apres confirmation du paiement.', N'pending', NULL, @now, @now);

INSERT INTO dbo.audit_logs (user_id, action, module, entity_type, entity_id, new_values, created_at, updated_at) VALUES
(2, N'demo.seed', N'simulation', N'vip_cards', 1, N'{"scenario":"active","card_number":"BUCA-VIP-0001"}', @now, @now),(2, N'demo.seed', N'simulation', N'vip_cards', 2, N'{"scenario":"low_balance","card_number":"BUCA-VIP-0002"}', @now, @now),(2, N'demo.seed', N'simulation', N'vip_cards', 3, N'{"scenario":"expired","card_number":"BUCA-VIP-0003"}', @now, @now),(2, N'demo.seed', N'simulation', N'vip_cards', 4, N'{"scenario":"suspended","card_number":"BUCA-VIP-0004"}', @now, @now),(2, N'demo.seed', N'simulation', N'vip_cards', 5, N'{"scenario":"pending_payment","card_number":"BUCA-VIP-0005"}', @now, @now);
GO

SELECT 'Base buca_voyages_vip creee avec succes.' AS message;
SELECT name FROM sys.tables ORDER BY name;
GO

