-- ============================================
-- Crear usuario para la aplicación Laravel
-- ============================================

-- Crear usuario
CREATE USER IF NOT EXISTS 'bienestar_admin'@'localhost' 
IDENTIFIED BY 'Ba_2025_Secure!';

-- Otorgar permisos completos en la base de datos
GRANT ALL PRIVILEGES ON bienestar_animal.* 
TO 'bienestar_admin'@'localhost';

-- Si necesitas acceso desde cualquier host (para Docker, etc.)
CREATE USER IF NOT EXISTS 'bienestar_admin'@'%' 
IDENTIFIED BY 'Ba_2025_Secure!';

GRANT ALL PRIVILEGES ON bienestar_animal.* 
TO 'bienestar_admin'@'%';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar usuarios creados
SELECT User, Host FROM mysql.user WHERE User = 'bienestar_admin';