-- ============================================================
--  SUNTASTIC SUPPLIER — SUPABASE SQL SETUP
--  I-run ito sa Supabase SQL Editor
-- ============================================================

-- ⚠ KUNG MAYROON KA NANG EXISTING NA TABLE, gamitin ito:
-- ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS price NUMERIC(12,2) NOT NULL DEFAULT 0;
-- ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS amount NUMERIC(12,2) NOT NULL DEFAULT 0;

-- ============================================================
--  PARA SA BAGONG TABLE (wala pang existing):
-- ============================================================
CREATE TABLE suppliers (
    id              BIGSERIAL PRIMARY KEY,
    item_name       TEXT NOT NULL,
    quantity        INTEGER NOT NULL DEFAULT 0,
    price           NUMERIC(12, 2) NOT NULL DEFAULT 0,
    supplier_name   TEXT NOT NULL,
    contact_number  TEXT NOT NULL,
    created_at      TIMESTAMPTZ DEFAULT NOW(),
    updated_at      TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Auto-update ng updated_at
CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER set_updated_at
    BEFORE UPDATE ON suppliers
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

-- 3. I-enable ang Row Level Security (RLS)
ALTER TABLE suppliers ENABLE ROW LEVEL SECURITY;

-- 4. Gumawa ng policies para sa public access (para sa demo)
--    TANDAAN: Para sa production, gumamit ng authentication
CREATE POLICY "Allow all operations" ON suppliers
    FOR ALL
    TO anon
    USING (true)
    WITH CHECK (true);

-- 5. Sample data (optional)
INSERT INTO suppliers (item_name, quantity, price, supplier_name, contact_number) VALUES
    ('Laptop ASUS VivoBook',     25,  35999.00, 'Tech Solutions PH',       '0917-123-4567'),
    ('T-Shirt Plain White (M)',  150, 120.00,   'Fil-Garments Trading',    '0928-234-5678'),
    ('Bigas Premium 25kg',       80,  1350.00,  'Santos Rice Mill',        '0945-345-6789'),
    ('Ballpen Blue BIC',         500, 12.00,    'Office Depot Manila',     '0956-456-7890'),
    ('Kangkong (kilo)',          5,   45.00,    'Benguet Farms Supply',    '0967-567-8901');
