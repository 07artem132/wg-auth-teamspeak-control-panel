<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 14.02.2018
 * Time: 16:42
 */

namespace App\Http\Controllers;

use App\Services\WN8;

class test {

	function wn8() {
		echo (string) ( new WN8( 3528155,  30 ) );

		return;
	}
}