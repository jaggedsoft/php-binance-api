<?php 


function balanceHandler( array $json ) {
   $balances = [];
   foreach( $json as $item ) {
      $asset = $item->a;
      $available = $item->f;
      $onOrder = $item->l;
      $balances[ $asset ] = [
            "available" => $available,
            "onOrder" => $onOrder
      ];
   }
   return $balances;
}
   
$jsonobj = json_decode( json_encode( array(
      "a" => 1,
      "f" => 2,
      "l" => 3
) ) );

$arr = array();
$arr[] = $jsonobj;
$arr[] = $jsonobj;

balanceHandler( $arr );
