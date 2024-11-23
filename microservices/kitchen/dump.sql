CREATE SCHEMA IF NOT EXISTS kitchen;

-- utilizo enums, a modo de standarizar luego los tipos, y que se mas sencilla la comunicacion
-- entre los MS
CREATE TYPE recipe_enum AS ENUM (
    'ensalada_de_pollo',
    'sopa_de_vegetales',
    'papas_con_queso',
    'arroz_con_pollo',
    'hamburguesa',
    'ensalada_mixta'
);

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

CREATE TABLE IF NOT EXISTS kitchen.recipes (
    name recipe_enum PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS kitchen.recipe_ingredients (
    id SERIAL PRIMARY KEY,
    recipe_name recipe_enum REFERENCES kitchen.recipes(name),
    ingredient_name ingredient_enum NOT NULL,
    quantity_required INT NOT NULL CHECK (quantity_required > 0)
);

INSERT INTO kitchen.recipes (name) VALUES 
('ensalada_de_pollo'::recipe_enum), 
('sopa_de_vegetales'::recipe_enum), 
('papas_con_queso'::recipe_enum),
('arroz_con_pollo'::recipe_enum),
('hamburguesa'::recipe_enum),
('ensalada_mixta'::recipe_enum);

-- todos los ingredientes deben ser usados en minimo una receta
INSERT INTO kitchen.recipe_ingredients (recipe_name, ingredient_name, quantity_required) VALUES
-- Ensalada de Pollo
('ensalada_de_pollo'::recipe_enum, 'tomato', 2),
('ensalada_de_pollo'::recipe_enum, 'lemon', 1),
('ensalada_de_pollo'::recipe_enum, 'lettuce', 1),
('ensalada_de_pollo'::recipe_enum, 'chicken', 1),
-- Sopa de Vegetales
('sopa_de_vegetales'::recipe_enum, 'potato', 3),
('sopa_de_vegetales'::recipe_enum, 'onion', 1),
('sopa_de_vegetales'::recipe_enum, 'rice', 1),
-- Papas con Queso
('papas_con_queso'::recipe_enum, 'potato', 3),
('papas_con_queso'::recipe_enum, 'cheese', 2),
-- Arroz con Pollo
('arroz_con_pollo'::recipe_enum, 'rice', 2),
('arroz_con_pollo'::recipe_enum, 'chicken', 1),
-- Hamburguesa
('hamburguesa'::recipe_enum, 'meat', 1),
('hamburguesa'::recipe_enum, 'lettuce', 1),
('hamburguesa'::recipe_enum, 'tomato', 1),
('hamburguesa'::recipe_enum, 'onion', 1),
('hamburguesa'::recipe_enum, 'ketchup', 2),
-- Ensalada Mixta
('ensalada_mixta'::recipe_enum, 'lettuce', 1),
('ensalada_mixta'::recipe_enum, 'tomato', 1),
('ensalada_mixta'::recipe_enum, 'onion', 1),
('ensalada_mixta'::recipe_enum, 'cheese', 1),
('ensalada_mixta'::recipe_enum, 'lemon', 1);
