-- Tabela de marcas
CREATE TABLE IF NOT EXISTS brands (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    codigo int NOT NULL,
    type varchar(100),
    fipe_code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- user

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(250),
    password VARCHAR(250),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de veículos

CREATE TABLE IF NOT EXISTS vehicles (
    id SERIAL PRIMARY KEY,
    brand_id int4,
    model varchar(200) NOT NULL,
    year int4,
    fipe_code varchar(50) NOT NULL,
    user_id int4,
    observations text,
    valor varchar(150),
    marca varchar(150),
    ano_modelo int4,
    mes_referencia varchar(150),
    sigla_combustivel varchar(10),
    modelo_id int4,
    ano varchar(50),
    tipo varchar(20),
    created_at timestamp(6) DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(6) DEFAULT CURRENT_TIMESTAMP
)

DO $$ 
BEGIN
    RAISE NOTICE 'Tabelas criadas com sucesso!';
END $$;  -- ✅ CORRETO: END $$;