CREATE SCHEMA IF NOT EXISTS store;

CREATE TYPE ingredient_enum AS ENUM (
    'tomato',
    'lemon',
    'potato',
    'rice',
    'ketchup',
    'lettuce',
    'onion',
    'cheese',
    'meat',
    'chicken'
);

CREATE TABLE IF NOT EXISTS store.ingredients (
    name ingredient_enum PRIMARY KEY,
    current_stock INT DEFAULT 5 CHECK (current_stock >= 0),
    created_at TIMESTAMP(6) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(6) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO store.ingredients (name, current_stock) VALUES 
('tomato'::ingredient_enum, 5), 
('lemon'::ingredient_enum, 5), 
('potato'::ingredient_enum, 5), 
('rice'::ingredient_enum, 5),
('ketchup'::ingredient_enum, 5), 
('lettuce'::ingredient_enum, 5), 
('onion'::ingredient_enum, 5), 
('cheese'::ingredient_enum, 5),
('meat'::ingredient_enum, 5), 
('chicken'::ingredient_enum, 5);
