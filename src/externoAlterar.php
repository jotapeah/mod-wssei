<?php 

$file = file_get_contents("/opt/sei/web/modulos/mod-wssei/teste.pdf");
//$file = file_get_contents("/opt/sei/web/modulos/mod-wssei/c.pdf");

$ch = curl_init("http://192.168.99.100/sei/modulos/mod-wssei/controlador_ws.php/api/v1/documento/externo/alterar");

//distribuindo a informa��o a ser enviada
$post = array(
    'documento'             => '241',
    'data'                  => '31/01/2017',
    'idTipoDocumento'       => '106',
    'numero'                => '12321313',
    'nomeArquivo'           => 'teste.pdf',
    'nivelAcesso'           => '1',
    'hipoteseLegal'         => '1',
    'grauSigilo'            => '',
    'assuntos'              => '[{"id": 79}]',
    'interessados'          => '[{"id": 100000012 },{"id":100000044}]',
    'destinatarios'         => '[{"id":100000044}]',
    'remetentes'            => '[{"id":100000044}]',
    'conteudoDocumento'     => $file,
//    'conteudoDocumento'     => "",
    'observacao'            => 'pa�oca',
    'tipoConferencia'       => '3',
);
 
$headers = array();

curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('token: YTRhZDBmOTEyYjUxY2MzYTgzNjc3NDMwNWNjM2JiMzFmY2U4ZTkxYmFUVnhUV2sxYnoxOGZHazFjVTFwTlc4OWZId3dmSHc9'));

$data = curl_exec($ch);
 
//Fecha a conex�o para economizar recursos do servidor
curl_close($ch);

var_dump($data);
die();
 
?>
