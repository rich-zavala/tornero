/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50620
Source Host           : 127.0.0.1:3306
Source Database       : tornero_2016

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2016-08-05 20:49:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for -pedidos
-- ----------------------------
DROP TABLE IF EXISTS `-pedidos`;
CREATE TABLE `-pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `almacen` int(11) NOT NULL DEFAULT '0',
  `comentario` text,
  `id_producto` int(11) NOT NULL DEFAULT '0',
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `costo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva` int(3) NOT NULL,
  `sub_importe` decimal(10,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `proveedor` int(11) NOT NULL DEFAULT '0',
  `obtenidos` decimal(10,3) NOT NULL DEFAULT '0.000',
  `especial` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT '0',
  `complemento` text CHARACTER SET latin1 COLLATE latin1_bin,
  `compra` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `moneda` char(6) NOT NULL DEFAULT 'M.N.',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `folio` (`folio`),
  KEY `fecha` (`fecha`),
  KEY `almacen` (`almacen`),
  KEY `id_producto` (`id_producto`),
  KEY `cantidad` (`cantidad`),
  KEY `costo` (`costo`),
  KEY `importe` (`importe`),
  KEY `proveedor` (`proveedor`),
  KEY `obtenidos` (`obtenidos`),
  KEY `cancelado` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=17647 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for alarm
-- ----------------------------
DROP TABLE IF EXISTS `alarm`;
CREATE TABLE `alarm` (
  `x` int(1) NOT NULL DEFAULT '0',
  `y` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`x`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for almacenes
-- ----------------------------
DROP TABLE IF EXISTS `almacenes`;
CREATE TABLE `almacenes` (
  `id_almacen` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0: Normal | 1:Eliminado',
  PRIMARY KEY (`id_almacen`),
  KEY `id_almacen` (`id_almacen`),
  KEY `descripcion` (`descripcion`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for bancos
-- ----------------------------
DROP TABLE IF EXISTS `bancos`;
CREATE TABLE `bancos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `titular` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `saldo` double(20,2) NOT NULL DEFAULT '0.00',
  `moneda` int(1) NOT NULL DEFAULT '0',
  `fecha_x` int(11) NOT NULL DEFAULT '324',
  `fecha_y` int(11) NOT NULL DEFAULT '17',
  `fecha_ancho` int(11) NOT NULL DEFAULT '208',
  `fecha_alto` int(11) NOT NULL DEFAULT '18',
  `beneficiario_x` int(11) NOT NULL DEFAULT '119',
  `beneficiario_y` int(11) NOT NULL DEFAULT '76',
  `beneficiario_alto` int(11) NOT NULL DEFAULT '18',
  `beneficiario_ancho` int(11) NOT NULL DEFAULT '278',
  `cantidad_numero_x` int(11) NOT NULL DEFAULT '406',
  `cantidad_numero_y` int(11) NOT NULL DEFAULT '76',
  `cantidad_numero_alto` int(11) NOT NULL DEFAULT '18',
  `cantidad_numero_ancho` int(11) NOT NULL DEFAULT '126',
  `cantidad_letras_x` int(11) NOT NULL DEFAULT '73',
  `cantidad_letras_y` int(11) NOT NULL DEFAULT '106',
  `cantidad_letras_alto` int(11) NOT NULL DEFAULT '18',
  `cantidad_letras_ancho` int(11) NOT NULL DEFAULT '460',
  `resumen_x` int(11) NOT NULL DEFAULT '46',
  `resumen_y` int(11) NOT NULL DEFAULT '225',
  `resumen_alto` int(11) NOT NULL DEFAULT '473',
  `resumen_ancho` int(11) NOT NULL DEFAULT '517',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `nombre` (`nombre`),
  KEY `titular` (`titular`),
  KEY `saldo` (`saldo`),
  KEY `moneda` (`moneda`),
  KEY `fecha` (`fecha_x`),
  KEY `beneficiario` (`beneficiario_x`),
  KEY `cantidad_numero` (`cantidad_numero_x`),
  KEY `cantidad_letras` (`cantidad_letras_x`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for cfdi
-- ----------------------------
DROP TABLE IF EXISTS `cfdi`;
CREATE TABLE `cfdi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` int(11) DEFAULT NULL,
  `serie` varchar(255) DEFAULT NULL,
  `xml` text,
  `pdf` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cfdi_unique` (`folio`,`serie`)
) ENGINE=InnoDB AUTO_INCREMENT=27245 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for clientes
-- ----------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `clave` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `telefonos` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `telefono2` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `calle` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `noexterior` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `nointerior` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `colonia` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `localidad` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `municipio` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `estado` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `pais` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `cp` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `RFC` varchar(20) COLLATE latin1_general_ci DEFAULT '',
  `vendedor` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `credito` decimal(30,2) DEFAULT '0.00',
  `credito_disponible` decimal(30,2) DEFAULT '0.00',
  `grupo` varchar(30) COLLATE latin1_general_ci DEFAULT '',
  `observaciones` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `dias_credito` int(3) DEFAULT '30',
  `email` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `NumCtaPago` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT 'NO IDENTIFICADO',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`clave`),
  KEY `cli_cl` (`clave`),
  KEY `cli_st` (`status`),
  KEY `cli_no` (`nombre`),
  KEY `cli_pais` (`pais`)
) ENGINE=MyISAM AUTO_INCREMENT=7518 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for clientes_memory
-- ----------------------------
DROP TABLE IF EXISTS `clientes_memory`;
CREATE TABLE `clientes_memory` (
  `clave` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `telefonos` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `telefono2` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `calle` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `noexterior` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `nointerior` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `colonia` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `localidad` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `municipio` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `estado` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `pais` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `cp` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `RFC` varchar(20) COLLATE latin1_general_ci DEFAULT '',
  `vendedor` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `credito` decimal(30,2) DEFAULT '0.00',
  `credito_disponible` decimal(30,2) DEFAULT '0.00',
  `grupo` varchar(30) COLLATE latin1_general_ci DEFAULT '',
  `observaciones` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `dias_credito` int(3) DEFAULT '30',
  `email` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `NumCtaPago` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT 'NO IDENTIFICADO',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`clave`),
  KEY `cli_cl` (`clave`),
  KEY `cli_st` (`status`),
  KEY `cli_no` (`nombre`),
  KEY `cli_pais` (`pais`)
) ENGINE=MEMORY AUTO_INCREMENT=7518 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for compras
-- ----------------------------
DROP TABLE IF EXISTS `compras`;
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio_factura` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `id_proveedor` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_almacen` int(10) NOT NULL DEFAULT '1',
  `fecha_factura` date NOT NULL DEFAULT '0000-00-00',
  `fecha_captura` date NOT NULL,
  `dias_credito` int(11) NOT NULL DEFAULT '0',
  `moneda` char(6) CHARACTER SET latin1 NOT NULL DEFAULT 'M.N.',
  `status` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `candado` (`folio_factura`,`id_proveedor`),
  KEY `folio_factura` (`folio_factura`),
  KEY `monto` (`importe`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `id_almacen` (`id_almacen`),
  KEY `fecha_factura` (`fecha_factura`),
  KEY `dias_credito` (`dias_credito`),
  KEY `cancelado` (`status`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2458 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for compras_detalle
-- ----------------------------
DROP TABLE IF EXISTS `compras_detalle`;
CREATE TABLE `compras_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_compra` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `id_producto` int(11) NOT NULL,
  `lote` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sub_importe` decimal(10,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10119 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for contrarecibos
-- ----------------------------
DROP TABLE IF EXISTS `contrarecibos`;
CREATE TABLE `contrarecibos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL DEFAULT '0',
  `proveedor` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL DEFAULT '0',
  `id_proveedor` int(11) NOT NULL DEFAULT '0',
  `fecha` date NOT NULL DEFAULT '0000-00-00',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0: Normal | 1: Pagado | 2: Cancelado',
  `tipo` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `proveedor` (`proveedor`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `fecha` (`fecha`),
  KEY `estado` (`status`),
  KEY `tipo` (`tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for contrarecibos_detalle
-- ----------------------------
DROP TABLE IF EXISTS `contrarecibos_detalle`;
CREATE TABLE `contrarecibos_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contrarecibo` int(11) NOT NULL DEFAULT '0',
  `folio` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `fecha` date NOT NULL,
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_contrarecibo` (`id_contrarecibo`),
  KEY `folio` (`folio`),
  KEY `monto` (`importe`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for conversiones
-- ----------------------------
DROP TABLE IF EXISTS `conversiones`;
CREATE TABLE `conversiones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `usuario` int(11) NOT NULL,
  `merma` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `lote` varchar(255) CHARACTER SET latin1 NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `cantidad_merma` decimal(10,3) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for cotizaciones
-- ----------------------------
DROP TABLE IF EXISTS `cotizaciones`;
CREATE TABLE `cotizaciones` (
  `folio` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_facturista` int(11) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cliente` text COLLATE latin1_general_ci NOT NULL,
  `datos_cliente` text COLLATE latin1_general_ci NOT NULL,
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `moneda` varchar(6) COLLATE latin1_general_ci NOT NULL DEFAULT 'M.N.',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0: Normal | 1: Cancelada',
  PRIMARY KEY (`folio`),
  KEY `folio` (`folio`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for cotizaciones_productos
-- ----------------------------
DROP TABLE IF EXISTS `cotizaciones_productos`;
CREATE TABLE `cotizaciones_productos` (
  `id_cotizacionproducto` int(11) NOT NULL AUTO_INCREMENT,
  `folio_cotizacion` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `unidad` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `especial` varchar(255) COLLATE latin1_general_ci DEFAULT '0',
  `complemento` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id_cotizacionproducto`)
) ENGINE=InnoDB AUTO_INCREMENT=58137 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for deprecated_notasconsecutivo
-- ----------------------------
DROP TABLE IF EXISTS `deprecated_notasconsecutivo`;
CREATE TABLE `deprecated_notasconsecutivo` (
  `nota` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`nota`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for devoluciones_clientes
-- ----------------------------
DROP TABLE IF EXISTS `devoluciones_clientes`;
CREATE TABLE `devoluciones_clientes` (
  `id` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `factura` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `fecha` (`fecha`),
  KEY `factura` (`factura`),
  KEY `monto` (`importe`),
  KEY `estado` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for devoluciones_clientes_detalles
-- ----------------------------
DROP TABLE IF EXISTS `devoluciones_clientes_detalles`;
CREATE TABLE `devoluciones_clientes_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_devolucion` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `id_producto` int(11) NOT NULL DEFAULT '0',
  `lote` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_devolucion` (`id_devolucion`),
  KEY `id_producto` (`id_producto`),
  KEY `lote` (`lote`),
  KEY `cantidad` (`cantidad`),
  KEY `precio` (`precio`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for devoluciones_proveedores
-- ----------------------------
DROP TABLE IF EXISTS `devoluciones_proveedores`;
CREATE TABLE `devoluciones_proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_compra` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `fecha` (`fecha`),
  KEY `id_compra` (`id_compra`),
  KEY `monto` (`importe`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for devoluciones_proveedores_detalles
-- ----------------------------
DROP TABLE IF EXISTS `devoluciones_proveedores_detalles`;
CREATE TABLE `devoluciones_proveedores_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_devolucion` int(11) NOT NULL DEFAULT '0',
  `id_producto` int(11) NOT NULL DEFAULT '0',
  `lote` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_devolucion` (`id_devolucion`),
  KEY `id_producto` (`id_producto`),
  KEY `lote` (`lote`),
  KEY `cantidad` (`cantidad`),
  KEY `precio` (`precio`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for egresos
-- ----------------------------
DROP TABLE IF EXISTS `egresos`;
CREATE TABLE `egresos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `beneficiario` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `banco` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fecha` date NOT NULL DEFAULT '0000-00-00',
  `tipo` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `tipo_bene` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `referencia` varchar(25) COLLATE latin1_general_ci NOT NULL DEFAULT '0',
  `descripcion` text COLLATE latin1_general_ci,
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `beneficiario` (`beneficiario`),
  KEY `monto` (`importe`),
  KEY `banco` (`banco`),
  KEY `fecha` (`fecha`),
  KEY `tipo_egreso` (`tipo`),
  KEY `tipo_bene` (`tipo_bene`),
  KEY `referencia` (`referencia`),
  KEY `cancelada` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=1246 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for egresos_detalle
-- ----------------------------
DROP TABLE IF EXISTS `egresos_detalle`;
CREATE TABLE `egresos_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT '0',
  `factura` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `abono` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_egreso` (`id_egreso`),
  KEY `factura` (`factura`),
  KEY `abono` (`abono`)
) ENGINE=MyISAM AUTO_INCREMENT=2486 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for entidades
-- ----------------------------
DROP TABLE IF EXISTS `entidades`;
CREATE TABLE `entidades` (
  `id_entidad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_entidad`),
  KEY `id_entidad` (`id_entidad`),
  KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for existencias
-- ----------------------------
DROP TABLE IF EXISTS `existencias`;
CREATE TABLE `existencias` (
  `id_existencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_almacen` int(10) unsigned NOT NULL DEFAULT '0',
  `id_producto` int(10) unsigned NOT NULL DEFAULT '0',
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `lote` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_existencia`),
  KEY `id_existencia` (`id_existencia`),
  KEY `id_almacen` (`id_almacen`),
  KEY `id_producto` (`id_producto`),
  KEY `cantidad` (`cantidad`),
  KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=6633 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for facturas
-- ----------------------------
DROP TABLE IF EXISTS `facturas`;
CREATE TABLE `facturas` (
  `folio` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `NumCtaPago` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT 'NO IDENTIFICADO',
  `metodoDePago` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT 'NO IDENTIFICADO',
  `moneda` char(6) COLLATE latin1_general_ci NOT NULL DEFAULT 'M.N.',
  `anoap` varchar(255) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Año de aprobación',
  `noap` varchar(255) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Número de aprobación',
  `nocertificado` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `id_facturista` int(10) unsigned NOT NULL DEFAULT '0',
  `importe` decimal(10,2) DEFAULT '0.00',
  `saldo` decimal(10,2) DEFAULT NULL,
  `serie` varchar(250) COLLATE latin1_general_ci DEFAULT NULL,
  `tipo` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `fecha_factura` date NOT NULL,
  `fecha_captura` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_cliente` int(10) unsigned DEFAULT '0',
  `datos_cliente` varchar(550) COLLATE latin1_general_ci DEFAULT '',
  `id_almacen` int(3) unsigned NOT NULL DEFAULT '0',
  `licitacion` varchar(255) COLLATE latin1_general_ci DEFAULT '',
  `leyenda` varchar(300) COLLATE latin1_general_ci DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0: Normal | 1: Cancelada',
  PRIMARY KEY (`folio`),
  UNIQUE KEY `folio` (`folio`,`serie`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_facturista` (`id_facturista`),
  KEY `id_almacen` (`id_almacen`),
  KEY `fecha_creacion` (`fecha_captura`),
  KEY `fs` (`folio`,`serie`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for facturas_productos
-- ----------------------------
DROP TABLE IF EXISTS `facturas_productos`;
CREATE TABLE `facturas_productos` (
  `id_facturaproducto` int(11) NOT NULL AUTO_INCREMENT,
  `folio_factura` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `serie` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `almacen` int(10) DEFAULT NULL,
  `usuario` int(10) DEFAULT NULL,
  `id_producto` int(11) NOT NULL,
  `lote` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `cantidad` decimal(10,3) NOT NULL COMMENT 'Cantidad de descuento en existencias',
  `canti_` decimal(10,3) DEFAULT '0.000' COMMENT 'Cantidad que se mostrará en la factura',
  `unidad` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `especial` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT '0',
  `complemento` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `precio` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva` decimal(10,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_facturaproducto`),
  KEY `fp_idp` (`id_producto`),
  KEY `fp_alm` (`almacen`),
  KEY `fp_im` (`importe`),
  KEY `fp_fol` (`folio_factura`,`serie`)
) ENGINE=MyISAM AUTO_INCREMENT=155008 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for ingresos
-- ----------------------------
DROP TABLE IF EXISTS `ingresos`;
CREATE TABLE `ingresos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `banco` int(11) NOT NULL DEFAULT '0',
  `tipo` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `referencia` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fecha` date NOT NULL DEFAULT '0000-00-00',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `monto` (`importe`),
  KEY `banco` (`banco`),
  KEY `tipo` (`tipo`),
  KEY `referencia` (`referencia`),
  KEY `fecha` (`fecha`),
  KEY `cancelada` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=71540 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for ingresos_detalle
-- ----------------------------
DROP TABLE IF EXISTS `ingresos_detalle`;
CREATE TABLE `ingresos_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ingreso` int(11) NOT NULL DEFAULT '0',
  `factura` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `abono` decimal(25,2) NOT NULL DEFAULT '0.00',
  `serie_factura` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_ingreso` (`id_ingreso`),
  KEY `factura` (`factura`),
  KEY `abono` (`abono`)
) ENGINE=MyISAM AUTO_INCREMENT=74520 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for mermas
-- ----------------------------
DROP TABLE IF EXISTS `mermas`;
CREATE TABLE `mermas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `usuario` int(11) NOT NULL,
  `almacen` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `lote` varchar(255) CHARACTER SET latin1 NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `disponible` decimal(10,3) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for minmax_productos
-- ----------------------------
DROP TABLE IF EXISTS `minmax_productos`;
CREATE TABLE `minmax_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `almacen` int(11) NOT NULL,
  `min` int(11) NOT NULL,
  `max` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2890 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for movimientos
-- ----------------------------
DROP TABLE IF EXISTS `movimientos`;
CREATE TABLE `movimientos` (
  `id_movimiento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_movimiento` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `folio` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `serie` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `id_almacen_origen` int(10) unsigned NOT NULL DEFAULT '0',
  `id_almacen_destino` int(10) unsigned NOT NULL DEFAULT '0',
  `id_tipomovimiento` int(10) unsigned NOT NULL DEFAULT '0',
  `id_almacen` int(10) unsigned NOT NULL DEFAULT '0',
  `id_producto` int(10) unsigned NOT NULL DEFAULT '0',
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `lote` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_usuario` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_movimiento`),
  KEY `id_movimiento` (`id_movimiento`),
  KEY `cantidad` (`cantidad`),
  KEY `fecha_movimiento` (`fecha_movimiento`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_almacen_origen` (`id_almacen`),
  KEY `mov_key` (`folio`,`serie`,`id_tipomovimiento`,`id_producto`,`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=162466 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for movimientos_bancos
-- ----------------------------
DROP TABLE IF EXISTS `movimientos_bancos`;
CREATE TABLE `movimientos_bancos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mov` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `banco` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fecha` date NOT NULL DEFAULT '0000-00-00',
  `ingresos` double(20,2) DEFAULT NULL,
  `egresos` double(20,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `banco` (`banco`),
  KEY `fecha` (`fecha`),
  KEY `ingresos` (`ingresos`),
  KEY `egresos` (`egresos`),
  KEY `id_mov` (`id_mov`)
) ENGINE=MyISAM AUTO_INCREMENT=72804 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for notas_consecutivo
-- ----------------------------
DROP TABLE IF EXISTS `notas_consecutivo`;
CREATE TABLE `notas_consecutivo` (
  `nota` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`nota`)
) ENGINE=InnoDB AUTO_INCREMENT=38011 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for notas_de_credito
-- ----------------------------
DROP TABLE IF EXISTS `notas_de_credito`;
CREATE TABLE `notas_de_credito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mov` int(11) NOT NULL DEFAULT '0',
  `folio` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `persona` int(11) NOT NULL,
  `reviso` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `autorizo` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_mov` (`id_mov`),
  KEY `tipo` (`tipo`),
  KEY `reviso` (`reviso`),
  KEY `autorizo` (`autorizo`),
  KEY `cancelada` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for notas_de_credito_detalle
-- ----------------------------
DROP TABLE IF EXISTS `notas_de_credito_detalle`;
CREATE TABLE `notas_de_credito_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nota` int(11) NOT NULL,
  `folio` varchar(255) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET latin1,
  `importe` decimal(10,2) NOT NULL,
  `serie` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for paises
-- ----------------------------
DROP TABLE IF EXISTS `paises`;
CREATE TABLE `paises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso_num` smallint(6) DEFAULT NULL,
  `iso2` char(2) CHARACTER SET latin1 DEFAULT NULL,
  `iso3` char(3) CHARACTER SET latin1 DEFAULT NULL,
  `pais_nombre` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idpais` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=241 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for pedidos
-- ----------------------------
DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `fecha` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `almacen` int(11) NOT NULL DEFAULT '0',
  `comentario` text CHARACTER SET latin1,
  `id_producto` int(11) NOT NULL DEFAULT '0',
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `costo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva` int(3) NOT NULL,
  `sub_importe` decimal(10,2) NOT NULL,
  `importe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `proveedor` int(11) NOT NULL DEFAULT '0',
  `obtenidos` decimal(10,3) NOT NULL DEFAULT '0.000',
  `especial` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT '0',
  `complemento` text CHARACTER SET latin1 COLLATE latin1_bin,
  `tipo` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `compra` text COLLATE latin1_general_ci,
  `moneda` char(6) CHARACTER SET latin1 NOT NULL DEFAULT 'M.N.',
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `folio` (`folio`),
  KEY `fecha` (`fecha`),
  KEY `almacen` (`almacen`),
  KEY `id_producto` (`id_producto`),
  KEY `cantidad` (`cantidad`),
  KEY `costo` (`costo`),
  KEY `importe` (`importe`),
  KEY `proveedor` (`proveedor`),
  KEY `obtenidos` (`obtenidos`),
  KEY `cancelado` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=30878 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for precios
-- ----------------------------
DROP TABLE IF EXISTS `precios`;
CREATE TABLE `precios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) DEFAULT '0',
  `cliente` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `precio` decimal(10,3) DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_producto` (`id_producto`),
  KEY `cliente` (`cliente`),
  KEY `precio` (`precio`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for precio_minimo
-- ----------------------------
DROP TABLE IF EXISTS `precio_minimo`;
CREATE TABLE `precio_minimo` (
  `id_producto` int(11) unsigned NOT NULL DEFAULT '0',
  `pmv` decimal(10,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id_producto`),
  KEY `id_producto` (`id_producto`),
  KEY `pmv` (`pmv`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for productos
-- ----------------------------
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id_producto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `codigo_barras` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `precio_publico` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `iva` decimal(2,0) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0: Activo | 1: Inactivo | 2: Eliminado',
  PRIMARY KEY (`id_producto`),
  KEY `id_producto` (`id_producto`),
  KEY `descripcion` (`descripcion`),
  KEY `codigo_barras` (`codigo_barras`),
  KEY `precio_publico` (`precio_publico`),
  KEY `iva` (`iva`),
  KEY `eliminado` (`status`)
) ENGINE=MEMORY AUTO_INCREMENT=8151 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for productos_solido
-- ----------------------------
DROP TABLE IF EXISTS `productos_solido`;
CREATE TABLE `productos_solido` (
  `id_producto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `codigo_barras` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `precio_publico` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `iva` decimal(2,0) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0: Activo | 1: Inactivo | 2: Eliminado',
  PRIMARY KEY (`id_producto`),
  KEY `id_producto` (`id_producto`),
  KEY `descripcion` (`descripcion`),
  KEY `codigo_barras` (`codigo_barras`),
  KEY `precio_publico` (`precio_publico`),
  KEY `iva` (`iva`),
  KEY `eliminado` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=8151 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for proveedores
-- ----------------------------
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `clave` int(20) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `telefonos` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `direccion` text COLLATE latin1_general_ci,
  `estado` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `ciudad` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `pais` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `cp` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `RFC` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `grupo` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `observaciones` text COLLATE latin1_general_ci,
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`clave`),
  KEY `pc` (`clave`)
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for retiros
-- ----------------------------
DROP TABLE IF EXISTS `retiros`;
CREATE TABLE `retiros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `almacen` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `orden` text CHARACTER SET latin1 NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1184 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for retiros_productos
-- ----------------------------
DROP TABLE IF EXISTS `retiros_productos`;
CREATE TABLE `retiros_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `retiro` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `lote` varchar(255) CHARACTER SET latin1 NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2086 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tipos_movimiento
-- ----------------------------
DROP TABLE IF EXISTS `tipos_movimiento`;
CREATE TABLE `tipos_movimiento` (
  `id_tipomovimiento` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `signo` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_tipomovimiento`),
  KEY `id_tipomovimiento` (`id_tipomovimiento`),
  KEY `descripcion` (`descripcion`),
  KEY `signo` (`signo`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for tipos_usuarios
-- ----------------------------
DROP TABLE IF EXISTS `tipos_usuarios`;
CREATE TABLE `tipos_usuarios` (
  `id_tipousuario` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `almacenes` int(1) NOT NULL DEFAULT '0' COMMENT '0=Todos los Almacenes | 1=Almenos un almacén',
  PRIMARY KEY (`id_tipousuario`),
  KEY `id_tipousuario` (`id_tipousuario`),
  KEY `descripcion` (`descripcion`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for traspasos
-- ----------------------------
DROP TABLE IF EXISTS `traspasos`;
CREATE TABLE `traspasos` (
  `id_traspaso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL DEFAULT '0',
  `id_almacen1` int(10) unsigned NOT NULL DEFAULT '0',
  `id_almacen2` int(1) NOT NULL DEFAULT '1',
  `fecha_creacion` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0: Normal | 1: Cancelado',
  PRIMARY KEY (`id_traspaso`),
  KEY `id_envio` (`id_traspaso`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_almacen1` (`id_almacen1`),
  KEY `id_almacen2` (`id_almacen2`),
  KEY `fecha_creacion` (`fecha_creacion`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for traspasos_productos
-- ----------------------------
DROP TABLE IF EXISTS `traspasos_productos`;
CREATE TABLE `traspasos_productos` (
  `id_traspasoproducto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_traspaso` int(10) unsigned NOT NULL DEFAULT '0',
  `id_producto` int(10) unsigned NOT NULL DEFAULT '0',
  `lote` varchar(10) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `cantidad` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id_traspasoproducto`),
  KEY `id_envioproducto` (`id_traspasoproducto`),
  KEY `id_envio` (`id_traspaso`),
  KEY `id_producto` (`id_producto`),
  KEY `lote` (`lote`),
  KEY `cantidad` (`cantidad`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `contrasena` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `nombre` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `correo` varchar(100) COLLATE latin1_general_ci DEFAULT '',
  `id_tipousuario` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `almacenes` varchar(250) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_entidad` int(10) unsigned NOT NULL DEFAULT '0',
  `fecha_registro` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0=Activo | 1=Inactivo',
  PRIMARY KEY (`id_usuario`),
  KEY `id_usuario` (`id_usuario`),
  KEY `username` (`username`),
  KEY `contrasena` (`contrasena`),
  KEY `nombres` (`nombre`),
  KEY `correo` (`correo`),
  KEY `fecha_registro` (`fecha_registro`),
  KEY `id_tipousuario` (`id_tipousuario`),
  KEY `id_almacen` (`almacenes`),
  KEY `id_entidad` (`id_entidad`),
  KEY `id_tipoestadousuario` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for vars
-- ----------------------------
DROP TABLE IF EXISTS `vars`;
CREATE TABLE `vars` (
  `nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `calle` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `noe` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `noi` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `colonia` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `localidad` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `municipio` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `estado` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `pais` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `cp` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `rfc` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `pmv` decimal(10,2) DEFAULT NULL,
  `logotipo` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smtp_usuario` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smtp_pass` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smtp_remitente` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smtp_puerto` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smtp_servidor` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smtp_autenticar` int(2) DEFAULT NULL,
  `mail_activo` int(11) DEFAULT NULL,
  `ncsd` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `anoa` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `noa` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `ruta` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `serie` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `folioi` int(15) DEFAULT NULL,
  `foliof` int(15) DEFAULT NULL,
  `dolar` decimal(10,2) NOT NULL,
  `cedula` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `fecha_inicio_cfdi` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for vectores
-- ----------------------------
DROP TABLE IF EXISTS `vectores`;
CREATE TABLE `vectores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(255) CHARACTER SET latin1 NOT NULL,
  `sector` varchar(255) CHARACTER SET latin1 NOT NULL,
  `alto` decimal(10,0) NOT NULL,
  `ancho` decimal(10,0) NOT NULL,
  `x` decimal(10,0) NOT NULL,
  `y` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for vectores_productos
-- ----------------------------
DROP TABLE IF EXISTS `vectores_productos`;
CREATE TABLE `vectores_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(255) CHARACTER SET latin1 NOT NULL,
  `columna` varchar(255) CHARACTER SET latin1 NOT NULL,
  `porcentaje` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- Table structure for _vars
-- ----------------------------
DROP TABLE IF EXISTS `_vars`;
CREATE TABLE `_vars` (
  `id` int(1) NOT NULL,
  `empresa` varchar(255) CHARACTER SET latin1 NOT NULL,
  `direccion` varchar(255) CHARACTER SET latin1 NOT NULL,
  `rfc` varchar(255) CHARACTER SET latin1 NOT NULL,
  `logotipo` varchar(255) CHARACTER SET latin1 NOT NULL,
  `pmv` decimal(10,2) NOT NULL DEFAULT '10.00',
  `dolar` decimal(10,2) NOT NULL DEFAULT '13.12',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- ----------------------------
-- View structure for clientes_pais
-- ----------------------------
DROP VIEW IF EXISTS `clientes_pais`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `clientes_pais` AS select `c`.`clave` AS `clave`,`c`.`nombre` AS `nombre`,`c`.`calle` AS `calle`,`c`.`noexterior` AS `noexterior`,`c`.`nointerior` AS `nointerior`,`c`.`colonia` AS `colonia`,`c`.`localidad` AS `localidad`,`c`.`municipio` AS `municipio`,`c`.`estado` AS `estado`,`c`.`cp` AS `cp`,`c`.`RFC` AS `RFC`,`c`.`NumCtaPago` AS `NumCtaPago`,`p`.`pais_nombre` AS `pais` from (`clientes_memory` `c` join `paises` `p` on((`p`.`id` = `c`.`pais`))) ;

-- ----------------------------
-- View structure for cotizaciones_productos_vista
-- ----------------------------
DROP VIEW IF EXISTS `cotizaciones_productos_vista`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER  VIEW `cotizaciones_productos_vista` AS select `cotizaciones_productos`.`id_cotizacionproducto` AS `id_cotizacionproducto`,`cotizaciones_productos`.`folio_cotizacion` AS `folio_cotizacion`,`cotizaciones_productos`.`id_producto` AS `id_producto`,`cotizaciones_productos`.`cantidad` AS `cantidad`,`cotizaciones_productos`.`unidad` AS `unidad`,`cotizaciones_productos`.`precio` AS `precio`,`cotizaciones_productos`.`iva` AS `iva`,`cotizaciones_productos`.`importe` AS `importe`,`cotizaciones_productos`.`especial` AS `especial`,`cotizaciones_productos`.`complemento` AS `complemento`,'' AS `lote` from `cotizaciones_productos` ;

-- ----------------------------
-- Procedure structure for clienteCredito
-- ----------------------------
DROP PROCEDURE IF EXISTS `clienteCredito`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `clienteCredito`(_clave INT)
BEGIN
	DECLARE _saldo DECIMAL(10, 2);  
	SELECT SUM(saldo) INTO _saldo FROM facturas WHERE id_cliente = _clave AND status <> 1;
	IF _saldo IS NULL THEN
		SET _saldo = 0;
	END IF;
	UPDATE clientes SET credito_disponible = credito - _saldo WHERE clave = _clave;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for clientes2Memory
-- ----------------------------
DROP PROCEDURE IF EXISTS `clientes2Memory`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `clientes2Memory`()
BEGIN
	DECLARE existe INT(1) DEFAULT (0);
	SELECT COUNT(1) INTO existe FROM information_schema.tables WHERE TABLE_NAME = 'clientes_memory' AND TABLE_SCHEMA = SCHEMA();
	IF existe = 0 THEN
		SET max_heap_table_size = 48 * 1024 * 1024;
		DROP TABLE IF EXISTS clientes_memory;
		CREATE TABLE clientes_memory LIKE clientes;
		ALTER TABLE clientes_memory ENGINE = MEMORY;
	ELSE
		TRUNCATE clientes_memory;
	END IF;

	INSERT IGNORE INTO clientes_memory SELECT * FROM clientes WHERE status = 0 ORDER BY nombre;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for compraCancelarProductos
-- ----------------------------
DROP PROCEDURE IF EXISTS `compraCancelarProductos`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `compraCancelarProductos`(compra INT(10))
BEGIN
	DECLARE done INT DEFAULT FALSE;
  DECLARE _producto INT;
  DECLARE _almacen INT;
  DECLARE _cantidad DECIMAL(10, 3);
  DECLARE _lote VARCHAR(50);

	DECLARE curs CURSOR FOR SELECT id_producto, cantidad, lote FROM compras_detalle WHERE id_compra = compra;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
	
	SELECT id_almacen INTO _almacen FROM compras WHERE id = compra;

	OPEN curs;
	read_loop: LOOP
		FETCH curs INTO _producto, _cantidad, _lote;
		IF done THEN
			LEAVE read_loop;
		END IF;
		CALL productoMovimiento(11, compra, NULL, _producto, _lote, _almacen, _cantidad, 0);
	END LOOP;
	CLOSE curs;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for cotizacionImporte
-- ----------------------------
DROP PROCEDURE IF EXISTS `cotizacionImporte`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `cotizacionImporte`(_folio varchar(10))
BEGIN
	DECLARE _importe DECIMAL(10, 2);

	SELECT ROUND( SUM( cantidad * ( precio + ( precio * ( iva / 100 ) ) ) ) , 2 ) INTO _importe
	FROM cotizaciones_productos WHERE
	folio_cotizacion = CONVERT(_folio using latin1) COLLATE latin1_general_ci;

	UPDATE cotizaciones SET importe = _importe WHERE
	folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for facturaCancelarProductos
-- ----------------------------
DROP PROCEDURE IF EXISTS `facturaCancelarProductos`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `facturaCancelarProductos`(_folio varchar(10), _serie varchar(10))
BEGIN
	DECLARE done INT DEFAULT FALSE;
  DECLARE _producto INT;
  DECLARE _almacen INT;
  DECLARE _cantidad DECIMAL(10, 3);
  DECLARE _lote VARCHAR(50);

	DECLARE curs CURSOR FOR SELECT id_producto, almacen, cantidad, lote FROM facturas_productos WHERE folio_factura = _folio AND
	(IF(_serie IS NOT NULL, _serie, 0) = 0 OR serie);
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	OPEN curs;
	read_loop: LOOP
		FETCH curs INTO _producto, _almacen, _cantidad, _lote;
		IF done THEN
			LEAVE read_loop;
		END IF;
		CALL productoMovimiento(10, _folio, _serie, _producto, _lote, _almacen, _cantidad, 0);
	END LOOP;
	CLOSE curs;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for facturaImporte
-- ----------------------------
DROP PROCEDURE IF EXISTS `facturaImporte`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `facturaImporte`(_folio varchar(10), _serie varchar(10))
BEGIN
	DECLARE factura_importe DECIMAL(10, 2);
	
	IF _serie IS NOT NULL THEN
		SELECT ROUND( SUM( canti_ * ( precio + ( precio * ( iva / 100 ) ) ) ) , 2 ) INTO factura_importe
		FROM facturas_productos WHERE
		folio_factura = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = CONVERT(_serie using latin1) COLLATE latin1_general_ci;

		UPDATE facturas SET importe = factura_importe WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = CONVERT(_serie using latin1) COLLATE latin1_general_ci;
	ELSE
		SELECT ROUND( SUM( canti_ * ( precio + ( precio * ( iva / 100 ) ) ) ) , 2 ) INTO factura_importe
		FROM facturas_productos WHERE
		folio_factura = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;

		UPDATE facturas SET importe = factura_importe WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;
	END IF;
	
	#CALL facturaSaldo(_folio, _serie);
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for facturaPagoContado
-- ----------------------------
DROP PROCEDURE IF EXISTS `facturaPagoContado`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `facturaPagoContado`(_folio varchar(10), _serie varchar(10))
BEGIN
	#Pagar factura de contado

	DECLARE factura_importe DECIMAL(10, 2);
	DECLARE factura_saldo DECIMAL(10, 2);
	DECLARE ingreso_id INT(10);

	#Obtener importe de factura
	IF _serie IS NOT NULL THEN
		SELECT importe, saldo INTO factura_importe, factura_saldo FROM facturas WHERE folio = _folio AND serie = serie;
	ELSE
		SELECT importe INTO factura_importe, factura_saldo FROM facturas WHERE folio = _folio AND serie IS NULL;
	END IF;

	#Sólo se pagan facturas con saldo = importe en banco "caja" (3) con referencia "PAGO DE CONTADO" y tipo "transferencia"
	SELECT factura_importe, factura_saldo;
	IF factura_importe = factura_saldo THEN
		INSERT INTO ingresos (importe, banco, tipo, referencia, fecha) VALUES (factura_importe, 3, "transferencia", "PAGO DE CONTADO", NOW());
		SET ingreso_id = LAST_INSERT_ID();
		INSERT INTO ingresos_detalle (id_ingreso, factura, abono, serie_factura) VALUES (ingreso_id, _folio, factura_importe, _serie);
		INSERT INTO movimientos_bancos (id_mov, banco, fecha, ingresos) VALUES (ingreso_id, 3, NOW(), factura_importe);
		#CALL facturaSaldo(_folio, _serie);
	END IF;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for facturaSaldo
-- ----------------------------
DROP PROCEDURE IF EXISTS `facturaSaldo`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `facturaSaldo`(_folio varchar(10), _serie varchar(10))
BEGIN
	DECLARE factura_status INT;
	DECLARE factura_importe DECIMAL(10, 2);
	DECLARE factura_abonos DECIMAL(10, 2);
	DECLARE _cliente INT;

	#Obtener cliente y status de la factura
	IF _serie IS NOT NULL THEN
		SELECT id_cliente, importe, status INTO _cliente, factura_importe, factura_status FROM facturas WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = IF(_serie IS NOT NULL, CONVERT(_serie using latin1) COLLATE latin1_general_ci, NULL);
	ELSE
		SELECT id_cliente, importe, status INTO _cliente, factura_importe, factura_status FROM facturas WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;
	END IF;

	#Abono es igual a importe si está cancelada (para que el saldo sea 0
	IF factura_status = 1 THEN
		SET factura_abonos = factura_importe;
	END IF;
	
	IF _serie IS NOT NULL THEN
		IF factura_status <> 1 THEN #Obtener abonos reales
			SELECT
			IFNULL(SUM(CASE WHEN i.status = 0 THEN id.abono ELSE 0 END),0) INTO factura_abonos
			FROM ingresos i INNER JOIN ingresos_detalle id ON i.id = id.id_ingreso WHERE
			id.factura = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
			serie_factura = IF(_serie IS NOT NULL, CONVERT(_serie using latin1) COLLATE latin1_general_ci, NULL);
		END IF;

		UPDATE facturas SET saldo = importe - factura_abonos WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie = CONVERT(_serie using latin1) COLLATE latin1_general_ci;
	ELSE
		IF factura_status <> 1 THEN #Obtener abonos reales
			SELECT
			IFNULL(SUM(CASE WHEN i.status = 0 THEN id.abono ELSE 0 END),0) INTO factura_abonos
			FROM ingresos i INNER JOIN ingresos_detalle id ON i.id = id.id_ingreso WHERE
			id.factura = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
			serie_factura IS NULL;
		END IF;

		UPDATE facturas SET saldo = factura_importe - factura_abonos WHERE
		folio = CONVERT(_folio using latin1) COLLATE latin1_general_ci AND
		serie IS NULL;
	END IF;

	#SELECT factura_importe, factura_abonos;
	#CALL clienteCredito(_cliente);
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for productoExistencia
-- ----------------------------
DROP PROCEDURE IF EXISTS `productoExistencia`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `productoExistencia`(tipo			INT(2)
,	producto 	INT(10)
, _lote 			VARCHAR(50)
, almacen 	INT(10)
, cant 	DECIMAL(10, 3))
BEGIN
	#Obtener multiplicador del catálogo de movimientos
	DECLARE _idex INT DEFAULT(0);
	DECLARE multiplicador INT DEFAULT(1);
	SELECT signo INTO multiplicador FROM tipos_movimiento WHERE id_tipomovimiento = tipo;

	#Definir cantidad
	SET cant = cant * multiplicador;
	
	#Identificar id de existencia
	SELECT id_existencia INTO _idex FROM existencias WHERE id_producto = producto AND lote = CONVERT(_lote using latin1) COLLATE latin1_general_ci AND id_almacen = almacen;
	IF _idex = 0 THEN
		INSERT INTO existencias (id_almacen, id_producto, cantidad, lote) VALUES (almacen, producto, cant, CONVERT(_lote using latin1) COLLATE latin1_general_ci);
	ELSE
		UPDATE existencias SET cantidad = cantidad + cant WHERE id_existencia = _idex;
	END IF;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for productoMovimiento
-- ----------------------------
DROP PROCEDURE IF EXISTS `productoMovimiento`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `productoMovimiento`(tipo			INT(2)
,	folio 		VARCHAR(10)
,	serie 		VARCHAR(10)
,	producto 	INT(10)
, lote 			VARCHAR(50)
, almacen 	DECIMAL(10,3)
, cantidad 	DECIMAL(10, 2)
, usuario 	INT(10))
BEGIN
	#Registrar movimiento
	IF producto > 0 THEN
		INSERT INTO movimientos
		(fecha_movimiento, folio, serie, id_tipomovimiento, id_almacen, id_producto, cantidad, lote, id_usuario)
		VALUES
		(NOW(), folio, serie, tipo, almacen, producto, cantidad, lote, usuario);
	END IF;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for productos2Memory
-- ----------------------------
DROP PROCEDURE IF EXISTS `productos2Memory`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `productos2Memory`()
BEGIN
	DECLARE existe INT(1) DEFAULT (0);
	SELECT COUNT(1) INTO existe FROM information_schema.tables WHERE TABLE_NAME = 'productos' AND TABLE_SCHEMA = SCHEMA();
	IF existe = 0 THEN
		SET max_heap_table_size = 48 * 1024 * 1024;
		DROP TABLE IF EXISTS productos;
		CREATE TABLE productos LIKE productos_solido;
		ALTER TABLE productos ENGINE = MEMORY;
	ELSE
		TRUNCATE productos;
	END IF;

	INSERT INTO productos SELECT * FROM productos_solido ORDER BY codigo_barras, descripcion;
END
;;
DELIMITER ;

-- ----------------------------
-- Function structure for clienteData
-- ----------------------------
DROP FUNCTION IF EXISTS `clienteData`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `clienteData`(_cliente INT(10)) RETURNS varchar(300) CHARSET latin1
BEGIN
	DECLARE cliente_data VARCHAR(300);
	SELECT CONCAT(
		IFNULL(RFC, ''), '|',
		IFNULL(nombre, ''), '|',
		IFNULL(calle, ''), '|',
		IFNULL(noexterior, ''), '|',
		IFNULL(nointerior, ''), '|',
		IFNULL(colonia, ''), '|',
		IFNULL(localidad, ''), '|',
		IFNULL(municipio, ''), '|',
		IFNULL(estado, ''), '|',
		IFNULL(pais, ''), '|',
		IFNULL(cp, ''))
	INTO cliente_data FROM clientes_pais WHERE clave = _cliente;
	RETURN cliente_data; 
END
;;
DELIMITER ;

-- ----------------------------
-- Function structure for productoImporte
-- ----------------------------
DROP FUNCTION IF EXISTS `productoImporte`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `productoImporte`(cantidad decimal(10,3), precio decimal(10,2), iva decimal(10,2)) RETURNS decimal(10,2)
BEGIN
	DECLARE importe DECIMAL(10, 2);
	RETURN ROUND(cantidad * precio, 2) + ROUND(cantidad * (precio * (iva / 100)), 2);
	#RETURN importe;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cli_cre_in`;
DELIMITER ;;
CREATE TRIGGER `cli_cre_in` BEFORE INSERT ON `clientes` FOR EACH ROW SET NEW.credito_disponible = NEW.credito
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cli_memo2`;
DELIMITER ;;
CREATE TRIGGER `cli_memo2` AFTER DELETE ON `clientes` FOR EACH ROW CALL clientes2Memory()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `c_del`;
DELIMITER ;;
CREATE TRIGGER `c_del` AFTER DELETE ON `compras` FOR EACH ROW DELETE FROM compras_detalle WHERE id_compra = OLD.id
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `com_mov_ins`;
DELIMITER ;;
CREATE TRIGGER `com_mov_ins` AFTER INSERT ON `compras_detalle` FOR EACH ROW BEGIN
SET @almacen = (SELECT id_almacen FROM compras WHERE id = NEW.id_compra);
CALL productoMovimiento(5, NEW.id_compra, NULL, NEW.id_producto, NEW.lote, @almacen, NEW.cantidad, 0);
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `del`;
DELIMITER ;;
CREATE TRIGGER `del` AFTER DELETE ON `cotizaciones` FOR EACH ROW DELETE FROM cotizaciones_productos WHERE folio_cotizacion = OLD.folio
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cot_ins_prod`;
DELIMITER ;;
CREATE TRIGGER `cot_ins_prod` BEFORE INSERT ON `cotizaciones_productos` FOR EACH ROW SET NEW.importe = productoImporte(NEW.cantidad,  NEW.precio, NEW.iva)
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cot_ins`;
DELIMITER ;;
CREATE TRIGGER `cot_ins` AFTER INSERT ON `cotizaciones_productos` FOR EACH ROW CALL cotizacionImporte(NEW.folio_cotizacion)
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cot_upd_prod`;
DELIMITER ;;
CREATE TRIGGER `cot_upd_prod` BEFORE UPDATE ON `cotizaciones_productos` FOR EACH ROW SET NEW.importe = productoImporte(NEW.cantidad,  NEW.precio, NEW.iva)
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cot_upd`;
DELIMITER ;;
CREATE TRIGGER `cot_upd` AFTER UPDATE ON `cotizaciones_productos` FOR EACH ROW CALL cotizacionImporte(NEW.folio_cotizacion)
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fac_fech`;
DELIMITER ;;
CREATE TRIGGER `fac_fech` BEFORE INSERT ON `facturas` FOR EACH ROW BEGIN
	SET NEW.fecha_captura = NOW(); 
	IF NEW.tipo = 'n' THEN
		SET NEW.id_cliente = 0;
		SET NEW.datos_cliente = '';
		SET NEW.leyenda = '';
		SET NEW.NumCtaPago = '';
		SET NEW.metodoDePago = ''; 
	ELSE
		SET NEW.datos_cliente = (SELECT clienteData(NEW.id_cliente));
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fac_canc`;
DELIMITER ;;
CREATE TRIGGER `fac_canc` BEFORE UPDATE ON `facturas` FOR EACH ROW BEGIN
	IF NEW.status = 1 THEN
		SET NEW.saldo = 0;
		CALL clienteCredito(NEW.id_cliente);
	END IF;
	IF NEW.id_cliente <> OLD.id_cliente THEN
		SET NEW.datos_cliente = (SELECT clienteData(NEW.id_cliente));
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fac_prod_del`;
DELIMITER ;;
CREATE TRIGGER `fac_prod_del` AFTER DELETE ON `facturas` FOR EACH ROW DELETE FROM facturas_productos WHERE folio_factura = OLD.folio AND serie = OLD.serie
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fpro_ins`;
DELIMITER ;;
CREATE TRIGGER `fpro_ins` BEFORE INSERT ON `facturas_productos` FOR EACH ROW BEGIN
	IF NEW.canti_ = 0 OR NEW.canti_ IS NULL THEN
		SET NEW.canti_ = NEW.cantidad;
	END IF;

	SET NEW.importe = productoImporte(NEW.canti_,  NEW.precio, NEW.iva);
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fpro_mov_ins`;
DELIMITER ;;
CREATE TRIGGER `fpro_mov_ins` AFTER INSERT ON `facturas_productos` FOR EACH ROW BEGIN
	IF NEW.serie IS NOT NULL THEN
		DELETE FROM movimientos WHERE id_movimiento = 9 AND folio = CONVERT(NEW.folio_factura using latin1) COLLATE latin1_general_ci
		AND serie = NEW.serie
		AND lote = CONVERT(NEW.lote using latin1) COLLATE latin1_general_ci;
	ELSE
		DELETE FROM movimientos WHERE id_movimiento = 9 AND folio = CONVERT(NEW.folio_factura using latin1) COLLATE latin1_general_ci
		AND serie IS NULL
		AND lote = CONVERT(NEW.lote using latin1) COLLATE latin1_general_ci;
	END IF;

	CALL facturaImporte(NEW.folio_factura, NEW.serie);
	CALL facturaSaldo(NEW.folio_factura, NEW.serie);
	IF NEW.cantidad > 0 THEN
		CALL productoMovimiento(9, NEW.folio_factura, NEW.serie, NEW.id_producto, NEW.lote, NEW.almacen, NEW.cantidad, NEW.usuario);
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fpro_upa`;
DELIMITER ;;
CREATE TRIGGER `fpro_upa` BEFORE UPDATE ON `facturas_productos` FOR EACH ROW BEGIN
	IF NEW.canti_ <> OLD.canti_ OR NEW.precio <> OLD.precio THEN
		SET NEW.importe = (SELECT productoImporte(NEW.canti_,  NEW.precio, NEW.iva));
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fpro_mov_upd`;
DELIMITER ;;
CREATE TRIGGER `fpro_mov_upd` AFTER UPDATE ON `facturas_productos` FOR EACH ROW BEGIN
	IF NEW.id_producto <> OLD.id_producto OR NEW.lote <> OLD.lote OR NEW.almacen <> OLD.almacen THEN
		
		SET @error = 1;
	ELSEIF NEW.cantidad <> OLD.cantidad THEN
		UPDATE movimientos SET cantidad = NEW.cantidad WHERE id_tipomovimiento = 9 AND folio = OLD.folio_factura AND serie = OLD.serie AND lote = OLD.lote;
	END IF;
	CALL facturaImporte(NEW.folio_factura, NEW.serie);
	CALL facturaSaldo(NEW.folio_factura, NEW.serie);
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `fpro_mov_del`;
DELIMITER ;;
CREATE TRIGGER `fpro_mov_del` AFTER DELETE ON `facturas_productos` FOR EACH ROW DELETE FROM movimientos WHERE
folio = OLD.folio_factura AND
serie = OLD.serie AND
id_producto = OLD.id_producto AND
lote = OLD.lote AND
id_almacen = OLD.almacen AND
(id_tipomovimiento = 9 OR id_tipomovimiento = 10)
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `mov_in`;
DELIMITER ;;
CREATE TRIGGER `mov_in` BEFORE INSERT ON `movimientos` FOR EACH ROW BEGIN
	IF NEW.id_producto > 0 THEN
		CALL productoExistencia(NEW.id_tipomovimiento, NEW.id_producto, NEW.lote, NEW.id_almacen, NEW.cantidad);
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `mov_upd`;
DELIMITER ;;
CREATE TRIGGER `mov_upd` BEFORE UPDATE ON `movimientos` FOR EACH ROW BEGIN
	IF NEW.id_producto <> OLD.id_producto OR NEW.lote <> OLD.lote OR NEW.id_almacen <> OLD.id_almacen THEN
		SET @error = 1;
	ELSEIF NEW.id_producto > 0 THEN
		SET @cantidad = OLD.cantidad - NEW.cantidad;
		CALL productoExistencia(NEW.id_tipomovimiento, NEW.id_producto, NEW.lote, NEW.id_almacen, @cantidad);
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `mov_del`;
DELIMITER ;;
CREATE TRIGGER `mov_del` BEFORE DELETE ON `movimientos` FOR EACH ROW BEGIN
	IF OLD.id_producto > 0 THEN
		CALL productoExistencia(OLD.id_tipomovimiento, OLD.id_producto, OLD.lote, OLD.id_almacen, ( OLD.cantidad * -1 ));
	END IF;
END
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `prod2solido`;
DELIMITER ;;
CREATE TRIGGER `prod2solido` AFTER UPDATE ON `productos` FOR EACH ROW BEGIN
	DELETE FROM productos_solido WHERE id_producto = NEW.id_producto;
	INSERT INTO productos_solido SELECT * FROM productos WHERE id_producto = NEW.id_producto;
END
;;
DELIMITER ;
