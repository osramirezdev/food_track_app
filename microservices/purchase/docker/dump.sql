CREATE SCHEMA IF NOT EXISTS purchase;

CREATE TABLE IF NOT EXISTS purchase.purchase_history (
    id SERIAL PRIMARY KEY,
    ingredient_name VARCHAR(50) NOT NULL,
    amount_purchased INT NOT NULL CHECK (amount_purchased > 0),
    created_at TIMESTAMP(6) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(6) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
