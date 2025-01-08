<?php
    
class WB_Token
{
    public static function decode_token($token)
    {
        $token_parts = explode(".", $token);
    
        $token_header = $token_parts[0];
        $token_header_json = base64_decode(strtr($token_header, '-_', '+/')); // Decode URL-safe base64
        $token_header_object = json_decode($token_header_json, true);

    
    
        $token_payload = $token_parts[1];
        $token_payload_json = base64_decode(strtr($token_payload, '-_', '+/')); // Decode URL-safe base64
        $token_payload_object = json_decode($token_payload_json, true);

        
        $supplier_id    = $token_payload_object['sid'];
        $scope          = $token_payload_object["s"];
        $expire         = $token_payload_object["exp"];

        
        $scope_string = self::decode_scope($scope);
        
        $diff = $token_payload_object["exp"] - time();
        
        $is_expired = ($diff < 86400) ? true : false;
        
        $token_data = array(
            "expire" => date("d.m.Y H:i:s", $expire),
            "scope" => $scope_string,
            "supplier_id" => $supplier_id
        );

        $result = array(
            "is_expired" => $is_expired,
            "token_data" => $token_data
        );

        /*
        $sheet->getCellByColumnAndRow(2, 8)->setValue($token_payload);
        $sheet->getCellByColumnAndRow(2, 9)->setValue($token_payload_object["ent"]);
        $sheet->getCellByColumnAndRow(2, 10)->setValue(date("Y-m-d H:i:s", strtotime("1970-01-01 +{$token_payload_object["exp"]} seconds")));
        $sheet->getCellByColumnAndRow(2, 11)->setValue($token_payload_object["id"]);
        $sheet->getCellByColumnAndRow(2, 12)->setValue($token_payload_object["iid"]);
        $sheet->getCellByColumnAndRow(2, 13)->setValue($token_payload_object["oid"]);
        $sheet->getCellByColumnAndRow(2, 14)->setValue($token_payload_object["s"]);
        $sheet->getCellByColumnAndRow(2, 15)->setValue($token_payload_object["sid"]);
        $sheet->getCellByColumnAndRow(2, 16)->setValue($token_payload_object["t"]);
        $sheet->getCellByColumnAndRow(2, 21)->setValue($token_payload_object["t"]);
        $sheet->getCellByColumnAndRow(2, 17)->setValue($token_payload_object["uid"]);
        */
    
        return $result;
    
    }
    
    private static function decode_scope($number)
    {
        $result = "";
        $Arr = array_fill(0, 12, "");
    
        if ($number & 2) $Arr[0] = "Контент";
        if ($number & 4) $Arr[1] = "Аналитика";
        if ($number & 8) $Arr[2] = "Цены и скидки";
        if ($number & 16) $Arr[3] = "Маркетплейс";
        if ($number & 32) $Arr[4] = "Статистика";
        if ($number & 64) $Arr[5] = "Продвижение";
        if ($number & 128) $Arr[6] = "Вопросы и отзывы";
        if ($number & 256) $Arr[7] = "Рекомендации";
        if ($number & 512) $Arr[8] = "Чат с покупателями";
        if ($number & 1024) $Arr[9] = "Поставки";
        if ($number & 2048) $Arr[10] = "Возвраты покупателями";
        if ($number & 4096) $Arr[11] = "Документы";
        if ($number > 1073741824) $Arr[12] = "READONLY";
           
        $result = implode(" | ", array_filter($Arr));
        
        return $result;
    }
}