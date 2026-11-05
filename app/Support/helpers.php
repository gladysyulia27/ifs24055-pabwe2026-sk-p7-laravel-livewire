<?php
if (! function_exists('stock_status_from_quantity')) { function stock_status_from_quantity(int $quantity,int $minimum): string { return $quantity<=0?'out_of_stock':($quantity<=$minimum?'low_stock':'available'); } }
