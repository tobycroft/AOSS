<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\index\model;

class ProjectModel {

	static $table = 'ao_project';

	public static function api_find($id) {
		$db = \think\Db::table(self::$table);
		$where = [
			'id' => $id,
		];
		$db->where($where);
		return $db->find();
	}

	public static function api_find_token($token) {
		$db = \think\Db::table(self::$table);
		$where = [
			'token' => $token,
			'status' => 1,
		];
		$db->where($where);
		return $db->find();
	}

}
