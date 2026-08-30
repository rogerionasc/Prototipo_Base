<?php
$json = '{"tuss_ids":[{"id":5569,"is_mapeamento":false,"tuss_mapeamento_id":null,"requer_autorizacao":false,"valor_ch":12,"valor_co":0},{"id":4953,"is_mapeamento":true,"tuss_mapeamento_id":1,"requer_autorizacao":false,"valor_ch":0.6,"valor_co":0}]}';
$data = json_decode($json, true);
$tussInput = $data['tuss_ids'];
$tussGridMap = [];
foreach ($tussInput as $item) {
    if (!empty($item['is_mapeamento']) && !empty($item['tuss_mapeamento_id'])) {
        if (isset($item['id'])) {
            $tussGridMap[(int)$item['id']] = ['is_mapeamento' => true, 'tuss_mapeamento_id' => (int)$item['tuss_mapeamento_id']];
        }
    } elseif(isset($item['id'])) {
        $tussGridMap[(int)$item['id']] = ['is_mapeamento' => false, 'tuss_id' => (int)$item['id']];
    }
}
print_r($tussGridMap);
