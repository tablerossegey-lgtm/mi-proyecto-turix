-- ==========================================
-- ESTRUCTURA DE LA TABLA: t_inventario_imagenes
-- ==========================================

-- Tabla secundaria para guardar más fotos asociadas a los productos de t_inventario.
-- Relación 1:N (Un producto puede tener múltiples fotos).

CREATE TABLE IF NOT EXISTS `t_inventario_imagenes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_producto` INT(11) NOT NULL,
  `ruta_foto` VARCHAR(255) NOT NULL,
  `orden` INT(11) NOT NULL DEFAULT 0,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`id_producto`),
  CONSTRAINT `fk_inventario_imagenes_producto` 
    FOREIGN KEY (`id_producto`) REFERENCES `t_inventario` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
