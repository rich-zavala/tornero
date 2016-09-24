/*
IMPORTANTE
Actualizar librerias/angular-templates


*/


ALTER TABLE `facturas`
ADD COLUMN `recargo_id`  int NULL AFTER `leyenda`,
ADD COLUMN `recargo_valor`  decimal(5,2) NOT NULL AFTER `recargo_id`;

ALTER TABLE `facturas`
MODIFY COLUMN `recargo_valor`  decimal(5,2) NOT NULL DEFAULT 0 AFTER `recargo_id`;

ALTER TABLE `facturas`
CHANGE COLUMN `recargo_valor` `recargo_porcentaje`  decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `recargo_id`,
ADD COLUMN `recargo_concepto`  varchar(255) NULL AFTER `recargo_id`,
ADD COLUMN `recargo_importe`  decimal(10,2) NULL AFTER `recargo_porcentaje`;

DROP PROCEDURE IF EXISTS `facturaImporte`;

CREATE PROCEDURE `facturaImporte`(_folio varchar(10), _serie varchar(10))
BEGIN
	DECLARE factura_importe DECIMAL(10, 2);
	DECLARE factura_recargo DECIMAL(10, 2);
	
	IF _serie IS NOT NULL THEN
		SELECT recargo_importe INTO factura_recargo FROM facturas
		WHERE folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = CONVERT(_serie using latin1) COLLATE latin1_general_ci;

		SELECT
		ROUND((ROUND(SUM(canti_ * precio), 2) + recargo_importe) + (((ROUND(SUM(canti_ * precio), 2 ) + recargo_importe)) * (iva / 100)), 2)
		INTO factura_importe
		FROM facturas_productos WHERE
		folio_factura = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = CONVERT(_serie using latin1) COLLATE latin1_general_ci;

		UPDATE facturas SET importe = factura_importe WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = CONVERT(_serie using latin1) COLLATE latin1_general_ci;
	ELSE
		SELECT recargo_importe INTO factura_recargo FROM facturas
		WHERE folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;

		SELECT
		ROUND((ROUND(SUM(canti_ * precio), 2) + recargo_importe) + (((ROUND(SUM(canti_ * precio), 2 ) + recargo_importe)) * (iva / 100)), 2)
		INTO factura_importe 
		FROM facturas_productos WHERE
		folio_factura = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;

		UPDATE facturas SET importe = factura_importe WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;
	END IF;
	
	#CALL facturaSaldo(_folio, _serie);
END;

ALTER TABLE `productos_solido`
ADD COLUMN `unidad`  varchar(255) NULL DEFAULT "PZA" AFTER `precio_publico`,
ADD INDEX `unidad` (`unidad`);

ALTER TABLE `productos`
ADD COLUMN `unidad`  varchar(255) NULL DEFAULT "PZA" AFTER `precio_publico`,
ADD INDEX `unidad` (`unidad`);

CREATE TRIGGER `instrim` BEFORE INSERT ON `productos_solido`
FOR EACH ROW BEGIN
SET NEW.descripcion = TRIM(CHAR(9) FROM TRIM(NEW.descripcion));
SET NEW.codigo_barras = TRIM(CHAR(9) FROM TRIM(NEW.codigo_barras));
END;

CREATE TRIGGER `updtrim` BEFORE UPDATE ON `productos_solido`
FOR EACH ROW BEGIN
SET NEW.descripcion = TRIM(CHAR(9) FROM TRIM(NEW.descripcion));
SET NEW.codigo_barras = TRIM(CHAR(9) FROM TRIM(NEW.codigo_barras));
END;

CREATE TRIGGER `instrimmem` BEFORE INSERT ON `productos`
FOR EACH ROW BEGIN
SET NEW.descripcion = TRIM(CHAR(9) FROM TRIM(NEW.descripcion));
SET NEW.codigo_barras = TRIM(CHAR(9) FROM TRIM(NEW.codigo_barras));
END;

CREATE TRIGGER `updtrimmem` BEFORE UPDATE ON `productos`
FOR EACH ROW BEGIN
SET NEW.descripcion = TRIM(NEW.descripcion);
SET NEW.codigo_barras = TRIM(NEW.codigo_barras);
END;

