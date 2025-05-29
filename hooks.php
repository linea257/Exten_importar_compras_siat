<?php

class hooks_importar_compras_siat extends hooks 
{
    var $module_name = "importar_compras_siat";
    
    function install_extension($check_only = true) 
    {
        error_log("Instalando extensión importar_compras_siat");
        
        if ($check_only) return true;
        
        // Crear tabla para las facturas de compra si no existe
        $this->create_database_tables();
        
        // Crear directorio para archivos importados
        $this->create_import_directories();
        
        // Registrar enlaces de menú
        $this->create_menu_entries();
        
        return true;
    }
    
    function uninstall_extension($check_only = true) 
    {
        error_log("Desinstalando extensión importar_compras_siat");
        
        if ($check_only) return true;
        
        // OPCIONAL: Eliminar tabla (comentado por seguridad)
        // $this->drop_database_tables();
        
        // Eliminar enlaces de menú
        $this->remove_menu_entries();
        
        return true;
    }
    
    function activate_extension($company, $check_only = true) 
    {
        error_log("Activando extensión importar_compras_siat para empresa: " . $company);
        
        if ($check_only) return true;
        
        // Verificar que existe la tabla
        $this->create_database_tables();
        
        // Registrar enlaces de menú
        $this->create_menu_entries();
        
        return true;
    }
    
    function deactivate_extension($company, $check_only = true) 
    {
        error_log("Desactivando extensión importar_compras_siat para empresa: " . $company);
        
        if ($check_only) return true;
        
        // Eliminar enlaces de menú
        $this->remove_menu_entries();
        
        return true;
    }
    
    // Crear tabla para facturas de compra
    private function create_database_tables() {
        global $db;
        
        $sql = "CREATE TABLE IF NOT EXISTS `0_facturas_compra` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nit_prov` varchar(20) DEFAULT NULL,
            `razon_social` varchar(255) DEFAULT NULL,
            `nro_auth` varchar(50) DEFAULT NULL,
            `nro_fact` varchar(50) DEFAULT NULL,
            `nro_pol` varchar(50) DEFAULT NULL,
            `fecha` date DEFAULT NULL,
            `importe` decimal(15,2) DEFAULT NULL,
            `imp_exc` decimal(15,2) DEFAULT NULL,
            `dbr` decimal(15,2) DEFAULT NULL,
            `imp_cred_fiscal` decimal(15,2) DEFAULT NULL,
            `tcompra` varchar(10) DEFAULT NULL,
            `cod_control` varchar(50) DEFAULT NULL,
            `debe1` varchar(20) DEFAULT NULL,
            `debe1_dimension1` int(11) DEFAULT NULL,
            `debe1_dimension2` int(11) DEFAULT NULL,
            `debe2` varchar(20) DEFAULT NULL,
            `debe2_dimension1` int(11) DEFAULT NULL,
            `debe2_dimension2` int(11) DEFAULT NULL,
            `haber` varchar(20) DEFAULT NULL,
            `haber_dimension1` int(11) DEFAULT NULL,
            `haber_dimension2` int(11) DEFAULT NULL,
            `memo` text DEFAULT NULL,
            `nro_trans` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_fecha` (`fecha`),
            KEY `idx_nit_prov` (`nit_prov`),
            KEY `idx_nro_fact` (`nro_fact`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
        
        $result = db_query($sql, "Error al crear tabla 0_facturas_compra");
        
        if ($result) {
            error_log("Tabla 0_facturas_compra creada/verificada correctamente");
        } else {
            error_log("Error al crear tabla 0_facturas_compra");
        }
        
        return $result;
    }
    
    // Crear directorios necesarios para importación
    private function create_import_directories() {
        global $path_to_root;
        
        $import_dir = $path_to_root . "/modules/importar_compras_siat/importados";
        $compras_dir = $import_dir . "/compras";
        
        if (!is_dir($import_dir)) {
            if (mkdir($import_dir, 0755, true)) {
                error_log("Directorio importados creado: " . $import_dir);
            } else {
                error_log("Error al crear directorio importados: " . $import_dir);
            }
        }
        
        if (!is_dir($compras_dir)) {
            if (mkdir($compras_dir, 0755, true)) {
                error_log("Directorio compras creado: " . $compras_dir);
            } else {
                error_log("Error al crear directorio compras: " . $compras_dir);
            }
        }
        
        // Crear archivo .htaccess para seguridad
        $htaccess_content = "deny from all\n";
        file_put_contents($import_dir . "/.htaccess", $htaccess_content);
    }
    
    // Eliminar tabla (CUIDADO: esto eliminará todos los datos)
    private function drop_database_tables() {
        $sql = "DROP TABLE IF EXISTS `0_facturas_compra`";
        $result = db_query($sql, "Error al eliminar tabla 0_facturas_compra");
        
        if ($result) {
            error_log("Tabla 0_facturas_compra eliminada");
        }
        
        return $result;
    }
    
    // Crear entradas de menú
    private function create_menu_entries() {
        global $path_to_root;
        
        if (function_exists('add_menu_item')) {
            // Añadir al menú de Compras
            add_menu_item(_("Importar Compras SIAT"), 
                 "SA_PAYMENT", 
                 "modules/importar_compras_siat/pages/importar_compras_siat.php", 
                 "Compras", 
                 "Transacciones");
                 
            error_log("Entrada de menú 'Importar Compras SIAT' añadida");
        } else {
            error_log("No se pudo añadir al menú - función add_menu_item no disponible");
        }
    }
    
    // Eliminar entradas de menú
    private function remove_menu_entries() {
        // Si FrontAccounting tiene una función para eliminar entradas de menú, úsala aquí
        if (function_exists('remove_menu_item')) {
            remove_menu_item("Importar Compras SIAT");
        }
        
        error_log("Entradas de menú eliminadas para importar_compras_siat");
    }
    
    // Hook opcional: ejecutar después de cada login
    function post_login_check() {
        // Verificar que los directorios existen
        $this->create_import_directories();
        
        // Verificar que la tabla existe
        $this->create_database_tables();
    }
}

?>