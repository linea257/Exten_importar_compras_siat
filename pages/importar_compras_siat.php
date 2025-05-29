<?php
$page_security = 'SA_PAYMENT';
$path_to_root = "../../..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = "Libro de compras IVA - V3 CSV"));

include_once($path_to_root . "/includes/ui.inc");
//include_once($path_to_root . "/gl/includes/gl_db.inc");
//include_once($path_to_root . "/includes/data_checks.inc");
//include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

include_once($path_to_root . "/includes/ui/items_cart.inc");

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/functions.inc.php");

include_once($path_to_root . "/gl/includes/gl_db.inc");

function parseCSV($rutaCSV){
    $handle = fopen($rutaCSV, "r");
    $filas = array(); //explode(chr(13).chr(10),$file);
    $cntTmp = 0;
    while (($line = fgets($handle)) !== false) {
        if($cntTmp>0){
            $csvReg = explode(",", $line);
            $csvIn_data_row = array();
            $csvIn_data_row["NRO"] = str_replace('"', '', $csvReg[0] ) ;
            $csvIn_data_row["NIT_PROVEEDOR"] = str_replace('"', '', $csvReg[1] ) ;
            $csvIn_data_row["RAZON_SOCIAL_PROVEEDOR"] = str_replace('"', '', $csvReg[2] ) ;
            $csvIn_data_row["CODIGO_DE_AUTORIZACION"] = str_replace('"', '', $csvReg[3] ) ;
            $csvIn_data_row["NUMERO_FACTURA"] = str_replace('"', '', $csvReg[4] ) ;
            $csvIn_data_row["NUMERO_DUI_DIM"] = str_replace('"', '', $csvReg[5] ) ;
            $csvIn_data_row["FECHA_DE_FACTURA_DUI_DIM"] = str_replace('"', '', $csvReg[6] ) ;
            $csvIn_data_row["IMPORTE_TOTAL_COMPRA"] = str_replace('"', '', $csvReg[7] ) ;
            $csvIn_data_row["IMPORTE_ICE"] = str_replace('"', '', $csvReg[8] ) ;
            $csvIn_data_row["IMPORTE_IEHD"] = str_replace('"', '', $csvReg[9] ) ;
            $csvIn_data_row["IMPORTE_IPJ"] = str_replace('"', '', $csvReg[10] ) ;
            $csvIn_data_row["TASAS"] = str_replace('"', '', $csvReg[11] ) ;
            $csvIn_data_row["OTRO_NO_SUJETO_A_CREDITO_FISCAL"] = str_replace('"', '', $csvReg[12] ) ;
            $csvIn_data_row["IMPORTES_EXENTOS"] = str_replace('"', '', $csvReg[13] ) ;
            $csvIn_data_row["IMPORTE_COMPRAS_GRAVADAS_A_TASA_CERO"] = str_replace('"', '', $csvReg[14] ) ;
            $csvIn_data_row["SUBTOTAL"] = str_replace('"', '', $csvReg[15] ) ;
            $csvIn_data_row["DESCUENTOS_BONIFICACIONES_REBAJAS_SUJETAS_AL_IVA"] = str_replace('"', '', $csvReg[16] ) ;
            $csvIn_data_row["IMPORTE_GIFT_CARD"] = str_replace('"', '', $csvReg[17] ) ;
            $csvIn_data_row["IMPORTE_BASE_CF"] = str_replace('"', '', $csvReg[18] ) ;
            $csvIn_data_row["CREDITO_FISCAL"] = str_replace('"', '', $csvReg[19] ) ;
            $csvIn_data_row["TIPO_COMPRA"] = str_replace('"', '', $csvReg[20] ) ;
            $csvIn_data_row["CODIGO_DE_CONTROL"] = str_replace('"', '', $csvReg[21] ) ;
            $csvIn_data_row["CON_DERECHO_A_CREDITO_FISCAL"] = str_replace('"', '', $csvReg[22] ) ;
            $csvIn_data_row["ESTADO_CONSOLIDACION"] = str_replace('"', '', $csvReg[23] ) ; 
            $filas[] = $csvIn_data_row;
        }
        $cntTmp++;
    }
    return $filas;
}


function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_code_id_edit');
}

extract($_POST);
if (isset($deletetable) && $deletetable) 
{
	$sqlDelete = "DELETE FROM `0_facturas_compra`";
	db_query($sqlDelete, "No se pudo eliminar");

}
check_db_has_gl_account_groups(_("There are no account groups defined. Please define at least one account group before entering accounts."));

$empresa = get_company_prefs();

?>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="../css/jquery.dataTables.min.css">
<div id="pdf"></div>
<form style="margin-left:10px" method="post" action="#" enctype="multipart/form-data">
	<div style="border:1px solid black; margin-left: 40px; width:300px; padding:10px">
		<label>Importar archivo CSV</label></br></br>
		<input type="hidden" name="accion" value="importar">
		<input id="importarTXT" name="archivo" type="file" value="Importar TXT"></br></br>
		<input type="submit" value="Importar">
	</div>
</form>
<form style="margin-left:10px" method="post" action="" enctype="multipart/form-data" onsubmit="return confirm('Esta seguro de eliminar las registros guardados?Esto reseteara las compras, sin eliminar los asientos ya generados.');">
	<div style="border:1px solid black; margin-left: 40px; width:300px; padding:10px">
		<input type="hidden" name="deletetable" value="1">	
		<input type="submit" value="Limpiar datos">
	</div>
</form>
<div id="contenedor">
	<center>
		<h1>Compras</h1>
	</center>
	<h4>Filtrar por fecha</h4>
<table>
<tr>
	<td>
		Fecha de inicio:
	</td>
	<td>
		<input type="date" id="fechaIni">
	</td>
</tr>
<tr>
	<td>
		Fecha fin:
	</td>
	<td>
		<input type="date" id="fechaFin">
	</td>
</tr>
<tr>
	<td colspan="2">
		<input id="filtrar" type="button" value="Filtrar">
	</td>
</tr>
</table>
</br></br>
<table id="tablaArticulos" class="bulk-selection-enabled">
		<thead>
		<tr>
			<!--
			<td>
            NRO1111
            </td>
            -->
            <td>
            NIT PROVEEDOR
            </td>
            <td>
            RAZON SOCIAL PROVEEDOR
            </td>
            <td>
            CODIGO DE AUTORIZACION
            </td>
            <td>
            NUMERO FACTURA
            </td>
            <td>
            NUMERO DUI/DIM
            </td>
            <td>
            FECHA DE FACTURA/DUI/DIM
            </td>
            <td>
            IMPORTE TOTAL COMPRA
            </td>
            <!--<td>
            IMPORTE ICE
            </td>
            -->
            <!--<td>
            IMPORTE IEHD
            </td>
            -->
            <!--<td>
            IMPORTE IPJ
            </td>
            -->
            <!--<td>
            TASAS
            </td>
            -->
            <td>
            OTRO NO SUJETO A CREDITO FISCAL
            </td>
            <!--<td>
            IMPORTES EXENTOS
            </td>
            -->
            <!--<td>
            IMPORTE COMPRAS GRAVADAS A TASA CERO
            </td>
            -->
            <!--<td>
            SUBTOTAL
            </td>
            -->
            <!--<td>
            DESCUENTOS BONIFICACIONES REBAJAS SUJETAS AL IVA
            </td>
            -->
            <!--<td>
            IMPORTE GIFT CARD
            </td>
            -->
            <td>
            IMPORTE BASE CF
            </td>
            <td>
            CREDITO FISCAL
            </td>
            <td>
            TIPO COMPRA
            </td>
            <td>
            CODIGO DE CONTROL
            </td>
            
            <!--<td>
            CON DERECHO A CREDITO FISCAL
            </td>
            -->
            <!--<td>
            ESTADO CONSOLIDACION
            </td>
			-->
			
			<td class="dropdown">
				Cuenta uno del DEBE
			</td>
			
			<td <?php if ($empresa['use_dimension'] < 1)
				{
					echo "style='display:none;'";
				}
				?>>
				Dimensi&oacute;n 1
			</td>
			<td <?php if ($empresa['use_dimension'] < 2)
				{
					echo "style='display:none;'";
				}
				?>>
				Dimensi&oacute;n 2
			</td>
			<td class="dropdown">
				Cuenta dos del DEBE - Cr&eacute;dito fiscal
			</td>
			<td <?php if ($empresa['use_dimension'] < 1)
				{
					echo "style='display:none;'";
				}
				?>>
				Dimensi&oacute;n 1
			</td>
			<td <?php if ($empresa['use_dimension'] < 2)
				{
					echo "style='display:none;'";
				}
				?>>
				Dimensi&oacute;n 2
			</td>
			<td class="dropdown">
				Cuenta del HABER
			</td>
			<td <?php if ($empresa['use_dimension'] < 1)
				{
					echo "style='display:none;'";
				}
				?>>
				Dimensi&oacute;n 1
			</td>
			<td <?php if ($empresa['use_dimension'] < 2)
				{
					echo "style='display:none;'";
				}
				?>>
				Dimensi&oacute;n 2
			</td>
			<td>
				Memorandum
			</td>
			
			<td>
				Contabilizar
			</td>
			
			
		</tr>
		</thead>
		<tbody>
			<?php
				$indice = 0;
				$sql = "SELECT * FROM `0_facturas_compra` ";
				$result = db_query($sql, "Error en obtener las facturas de compra");
				$ind = 1;

				while ($row = mysql_fetch_array($result)) {
					$indice++;
					echo "<tr id='fila".$indice."'>";
										
					echo "<td>";
					echo "<input type='text' value='".$row["nit_prov"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["razon_social"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["nro_auth"]."' disabled  >";  //prueba
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["nro_fact"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["nro_pol"]."' disabled >"; //prueba
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["fecha"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["importe"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["imp_exc"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["dbr"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["imp_cred_fiscal"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["tcompra"]."' disabled >"; 
					echo "</td>"; 


					echo "<td>";
					echo "<input type='text' value='".$row["cod_control"]."' disabled >" ;  //ocultar codigo de control
					echo "</td>"; 


					
					
					
					

					switch ($empresa['use_dimension']) {
						case 1:
							$display1 = true;
							$display2 = false;
							break;
						case 2:
							$display1 = true;
							$display2 = true;
							break;
						case 0:
							$display1 = false;
							$display2 = false;
							break;
					}

					echo generarCuentas($row['debe1'], false);

					echo generarDimensiones($row['debe1_dimension1'], false, $display1);

					echo generarDimensiones($row['debe1_dimension2'], false, $display2);

					echo generarCuentas($row['debe2'], false);

					echo generarDimensiones($row['debe2_dimension1'], false, $display1);

					echo generarDimensiones($row['debe2_dimension2'], false, $display2);

					echo generarCuentas($row['haber'], false);

					echo generarDimensiones($row['haber_dimension1'], false, $display1);

					echo generarDimensiones($row['haber_dimension2'], false, $display2);

					echo "<td>";
					echo "<input type='text' value='";
					echo $row['memo'];
					echo "' disabled></td>";

					echo "<td>";
					echo '<a target="_blank" href="../gl/view/gl_trans_view.php?type_id=0&trans_no='.$row['nro_trans'].'"><input type="button" value="Ver Asiento #'.$row['nro_trans'].'"></a>';
					echo "</td>";

					echo "</tr>";
				}


				if (isset($accion))
				{
				   
					if ($accion == 'importar')
					{
						//copy(,);
						move_uploaded_file($_FILES['archivo']['tmp_name'], "importados/compras/" .$_FILES['archivo']['name']);
						$file = file_get_contents("importados/compras/" .$_FILES['archivo']['name']);
						//$filas = explode(chr(13).chr(10),$file);
						
						$filas = parseCSV("importados/compras/" .$_FILES['archivo']['name']);
							
						for ($i=0; $i < count($filas); $i++)
						{
						    $columnas = $filas[$i]; //explode("|",$filas[$i]);
						
					

						if (isset($columnas["FECHA_DE_FACTURA_DUI_DIM"]) && $columnas["FECHA_DE_FACTURA_DUI_DIM"]!=null) {

							$fecha_transaccion = DateTime::createFromFormat('d/m/Y', $columnas["FECHA_DE_FACTURA_DUI_DIM"]);
							$columnas["FECHA_DE_FACTURA_DUI_DIM"] =  $fecha_transaccion->format('Y-m-d');

							$indice++;
							echo '<tr id="fila'.$indice.'" class="bulk-control-enabled">';
							?>
								<!--
								<td>
                                <input type="text" value="<?php echo $columnas["NRO"];?>" id="cel_reg_<?php echo $indice;?>_NRO" >
                                </td>
                                -->
                                <td>
                                <input type="text" value="<?php echo $columnas["NIT_PROVEEDOR"];?>" id="cel_reg_<?php echo $indice;?>_NIT_PROVEEDOR" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["RAZON_SOCIAL_PROVEEDOR"];?>" id="cel_reg_<?php echo $indice;?>_RAZON_SOCIAL_PROVEEDOR" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["CODIGO_DE_AUTORIZACION"];?>" id="cel_reg_<?php echo $indice;?>_CODIGO_DE_AUTORIZACION" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["NUMERO_FACTURA"];?>" id="cel_reg_<?php echo $indice;?>_NUMERO_FACTURA" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["NUMERO_DUI_DIM"];?>" id="cel_reg_<?php echo $indice;?>_NUMERO_DUI_DIM" >
                                </td>
                                <td>
                                <input type="date" style="width: 120px !important"  value="<?php echo $columnas["FECHA_DE_FACTURA_DUI_DIM"];?>" id="cel_reg_<?php echo $indice;?>_FECHA_DE_FACTURA_DUI_DIM" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_TOTAL_COMPRA"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_TOTAL_COMPRA" >
                                </td>
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_ICE"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_ICE" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_IEHD"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_IEHD" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_IPJ"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_IPJ" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["TASAS"];?>" id="cel_reg_<?php echo $indice;?>_TASAS" >
                                </td>
                                -->
                                <td>
                                <input type="text" value="<?php echo $columnas["OTRO_NO_SUJETO_A_CREDITO_FISCAL"];?>" id="cel_reg_<?php echo $indice;?>_OTRO_NO_SUJETO_A_CREDITO_FISCAL" >
                                </td>
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["IMPORTES_EXENTOS"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTES_EXENTOS" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_COMPRAS_GRAVADAS_A_TASA_CERO"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_COMPRAS_GRAVADAS_A_TASA_CERO" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["SUBTOTAL"];?>" id="cel_reg_<?php echo $indice;?>_SUBTOTAL" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["DESCUENTOS_BONIFICACIONES_REBAJAS_SUJETAS_AL_IVA"];?>" id="cel_reg_<?php echo $indice;?>_DESCUENTOS_BONIFICACIONES_REBAJAS_SUJETAS_AL_IVA" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_GIFT_CARD"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_GIFT_CARD" >
                                </td>
                                -->
                                <td>
                                <input type="text" value="<?php echo $columnas["IMPORTE_BASE_CF"];?>" id="cel_reg_<?php echo $indice;?>_IMPORTE_BASE_CF" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["CREDITO_FISCAL"];?>" id="cel_reg_<?php echo $indice;?>_CREDITO_FISCAL" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["TIPO_COMPRA"];?>" id="cel_reg_<?php echo $indice;?>_TIPO_COMPRA" >
                                </td>
                                <td>
                                <input type="text" value="<?php echo $columnas["CODIGO_DE_CONTROL"];?>" id="cel_reg_<?php echo $indice;?>_CODIGO_DE_CONTROL" >
                                </td>
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["CON_DERECHO_A_CREDITO_FISCAL"];?>" id="cel_reg_<?php echo $indice;?>_CON_DERECHO_A_CREDITO_FISCAL" >
                                </td>
                                -->
                                <!--<td>
                                <input type="text" value="<?php echo $columnas["ESTADO_CONSOLIDACION"];?>" id="cel_reg_<?php echo $indice;?>_ESTADO_CONSOLIDACION" >
                                </td>
                                -->

								
								
								<td>
									<input type="checkbox" class="cuenta-uno-debe" style="float: left; display: inline; width: auto;" />
									<select style="width: 130px" class="cuenta-uno-debe">
									<?php
									$select = "";
									$cuentas = get_gl_accounts(null,null,null);
									while ($row = mysql_fetch_array($cuentas))
									{
										$select .= "<option value='".$row['account_code']."'";
										$select .= ">".$row['account_name']."";
										$select .= "</option>";
									}
									echo $select;
									?>

									</select>
								</td>
								<td <?php if ($empresa['use_dimension'] < 1)
									{
										echo "style='display:none;'";
									}
									?>>
									<select>
									<?php
									$dimension = "";
									$dimensiones = get_the_dimensions();
									while ($row = mysql_fetch_array($dimensiones))
									{
										$dimension .= "<option value='".$row['id']."'";
										$dimension .= ">".$row['name']."";
										$dimension .= "</option>";
									}
									if ($empresa['use_dimension'] < 1)
									{
										echo "<option value='0'>Sin dimension</option>";
									}
									echo $dimension;
									?>
									</select>
								</td>
								<td <?php if ($empresa['use_dimension'] < 2)
									{
										echo "style='display:none;'";
									}
									?>>
									<select>
									<?php
										if ($empresa['use_dimension'] < 2)
										{
											echo "<option value='0'>Sin dimension</option>";
										}
										echo $dimension;
									?>
									</select>
								</td>
								<td>
									<input type="checkbox" class="cuenta-dos-debe" style="float: left; display: inline; width: auto;" />
									<select style="width: 130px" class="cuenta-dos-debe">
									<?php
									echo $select;
									?>

									</select>
								</td>
								<td <?php if ($empresa['use_dimension'] < 1)
									{
										echo "style='display:none;'";
									}
									?>>
									<select>
									<?php
										if ($empresa['use_dimension'] < 1)
										{
											echo "<option value='0'>Sin dimension</option>";
										}
										echo $dimension;
									?>
									</select>
								</td>
								<td <?php if ($empresa['use_dimension'] < 2)
									{
										echo "style='display:none;'";
									}
									?>>
									<select>
									<?php
										if ($empresa['use_dimension'] < 2)
										{
											echo "<option value='0'>Sin dimension</option>";
										}
										echo $dimension;
									?>
									</select>
								</td>
								<td>
									<input type="checkbox" class="cuenta-haber" style="float: left; display: inline; width: auto;" />
									<select style="width: 130px" class="cuenta-haber">
									<?php
									echo $select;
									?>

									</select>
								</td>
								<td <?php if ($empresa['use_dimension'] < 1)
									{
										echo "style='display:none;'";
									}
									?>>
									<select>
									<?php
										if ($empresa['use_dimension'] < 1)
										{
											echo "<option value='0'>Sin dimension</option>";
										}
										echo $dimension;
									?>
									</select>
								</td>
								<td <?php if ($empresa['use_dimension'] < 2)
									{
										echo "style='display:none;'";
									}
									?>>
									<select>
									<?php
										if ($empresa['use_dimension'] < 2)
										{
											echo "<option value='0'>Sin dimension</option>";
										}
										echo $dimension;
									?>
									</select>
								</td>
								<td>
									<input type="text" value="Por el registro de la factura de <?php echo $columnas["RAZON_SOCIAL_PROVEEDOR"] ?> Nro. <?php echo $columnas["NUMERO_FACTURA"] ?> de fecha <?php echo $columnas["FECHA_DE_FACTURA_DUI_DIM"] ?> por un importe de Bs <?php echo $columnas["IMPORTE_TOTAL_COMPRA"] ?>">
								</td>
								<td>
									<input type="button" class="contCSV" value="Contabilizar" indiceReg="<?php echo $indice;?>">
								</td>
								
					</tr>
						<?php
			}
						}
					}
				}
			?>
		</tbody>
	</table></br></br>

</div>
<input type='hidden' id='indice' value='<?php echo $indice;?>'>

</input>
<div style='margin-left: 70px;'>
	</br></br>
		<a href="#" id="otroPedido"><img src="../img/plus.png" width="25" height="25"></a>
		<a href="#" id="restarPedido"><img src="../img/minus.png" width="25" height="25"></a>
	</br></br>
		<input id="altaPedidoTxt" type="button" value="">
		<!-- <input id="altaPedidoPdf" type="button" value="Generar PDF"> -->
</div>
	</div>
</br></br>
<style type="text/css">
#tablaArticulos tr td input{
	width: 100px;
}
#contenedor{
	margin: 50px;
}
</style>
<table>
<tr id="fila" style="display:none">
			<td>
            NRO22222
            </td>
            <td>
            NIT PROVEEDOR
            </td>
            <td>
            RAZON SOCIAL PROVEEDOR
            </td>
            <td>
            CODIGO DE AUTORIZACION
            </td>
            <td>
            NUMERO FACTURA
            </td>
            <td>
            NUMERO DUI/DIM
            </td>
            <td>
            FECHA DE FACTURA/DUI/DIM
            </td>
            <td>
            IMPORTE TOTAL COMPRA
            </td>
            <td>
            IMPORTE ICE
            </td>
            <td>
            IMPORTE IEHD
            </td>
            <td>
            IMPORTE IPJ
            </td>
            <td>
            TASAS
            </td>
            <td>
            OTRO NO SUJETO A CREDITO FISCAL
            </td>
            <td>
            IMPORTES EXENTOS
            </td>
            <td>
            IMPORTE COMPRAS GRAVADAS A TASA CERO
            </td>
            <td>
            SUBTOTAL
            </td>
            <td>
            DESCUENTOS/BONIFICACIONES/REBAJAS SUJETAS AL IVA
            </td>
            <td>
            IMPORTE GIFT CARD
            </td>
            <td>
            IMPORTE BASE CF
            </td>
            <td>
            CREDITO FISCAL
            </td>
            <td>
            TIPO COMPRA
            </td>
            <td>
            CODIGO DE CONTROL
            </td>
            <td>
            CON DERECHO A CREDITO FISCAL
            </td>
            <td>
            ESTADO CONSOLIDACION
            </td>
			<!--  
			<td>
				<select>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4/option>
					<option value="5">5</option>
				</select>
			</td>
			<td>
				<select style="width: 150px">
				<?php
				$select = "";
				$cuentas = get_gl_accounts(null,null,null);
				while ($row = mysql_fetch_array($cuentas))
				{
					$select .= "<option value='".$row['account_code']."'";
					$select .= ">".$row['account_name']."";
					$select .= "</option>";
				}
				echo $select;
				?>

				</select>
			</td>
			<td <?php if ($empresa['use_dimension'] < 1)
				{
					echo "style='display:none;'";
				}
				?>>
				<select>
				<?php
				$dimension = "";
				$dimensiones = get_the_dimensions();
				while ($row = mysql_fetch_array($dimensiones))
				{
					$dimension .= "<option value='".$row['id']."'";
					$dimension .= ">".$row['name']."";
					$dimension .= "</option>";
				}
				if ($empresa['use_dimension'] < 1)
				{
					echo "<option value='0'>Sin dimension</option>";
				}
				echo $dimension;
				?>
				</select>
			</td>
			<td <?php if ($empresa['use_dimension'] < 2)
				{
					echo "style='display:none;'";
				}
				?>>
				<select>
				<?php
					if ($empresa['use_dimension'] < 2)
					{
						echo "<option value='0'>Sin dimension</option>";
					}
					echo $dimension;
				?>
				</select>
			</td>
			<td>
				<select style="width: 150px">
				<?php
				echo $select;
				?>

				</select>
			</td>
			<td <?php if ($empresa['use_dimension'] < 1)
				{
					echo "style='display:none;'";
				}
				?>>
				<select>
				<?php
					if ($empresa['use_dimension'] < 1)
					{
						echo "<option value='0'>Sin dimension</option>";
					}
					echo $dimension;
				?>
				</select>
			</td>
			<td <?php if ($empresa['use_dimension'] < 2)
				{
					echo "style='display:none;'";
				}
				?>>
				<select>
				<?php
					if ($empresa['use_dimension'] < 2)
					{
						echo "<option value='0'>Sin dimension</option>";
					}
					echo $dimension;
				?>
				</select>
			</td>
			<td>
				<select style="width: 150px">
				<?php
				echo $select;
				?>

				</select>
			</td>
			<td <?php if ($empresa['use_dimension'] < 1)
				{
					echo "style='display:none;'";
				}
				?>>
				<select>
				<?php
					if ($empresa['use_dimension'] < 1)
					{
						echo "<option value='0'>Sin dimension</option>";
					}
					echo $dimension;
				?>
				</select>
			</td>
			<td <?php if ($empresa['use_dimension'] < 2)
				{
					echo "style='display:none;'";
				}
				?>>
				<select>
				<?php
					if ($empresa['use_dimension'] < 2)
					{
						echo "<option value='0'>Sin dimension</option>";
					}
					echo $dimension;
				?>
				</select>
			</td>
			<td>
				<input type="text">
			</td>
			<td>
				<input type="button" class="contCSV" value="Contabilizar">
			</td>
			
			-->
</tr>
</table>
<script type="text/javascript">
	var editors = "";
</script>
<script type="text/javascript" src="../js/jquery.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../js/datatableinput.js"></script>
<script type="text/javascript" src="../js/booksCSV-3.js?ver=1.2"></script> 
<script type="text/javascript" src="../js/bulk-selection.js"></script>
