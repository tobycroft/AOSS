<?php

namespace app\index\controller;

use think\Request;

class Index extends \think\Controller {

	public $token;

	public function initialize() {
		header('Access-Control-Allow-Origin:*');
		$this->token = input('get.token');
		if (!$this->token) {
			$this->fail('token');
		}
	}

	public function index() {
		dump(config('aliyun.'));
	}

	public function upload_file(Request $request, $full = 0, $ue = 0) {
		$token = $this->token;
		$proc = \app\index\model\ProjectModel::api_find_token($token);
		if (!$proc) {
			$this->fail('项目不可用');
		}
		$oss = new \OSS\AliyunOSS($proc);
		$file = $request->file('file');
		if (!$file) {
			$this->fail('file字段没有用文件提交');
		}
		$info = $file->validate(['size' => (float) $proc['size'] * 1024, 'ext' => $proc['ext']])->move('./upload');
		if ($info) {
			$fileName = $proc['name'] . '/' . $info->getSaveName();
			$type = explode(',', $proc['type']);
			if (in_array('oss', $type)) {
				$oss->uploadFile($proc['bucket'], $fileName, $info->getPathname());
				if ($proc['main_type'] == 'oss') {
					$sav = ($full ? $proc['url'] . '/' : '' ) . $fileName;
				}
			}
			if (!in_array('local', $type)) {
				unlink($info->getPathname());
			} else {
				if ($proc['main_type'] == 'local') {
					$sav = ($full ? $proc['url'] . '/' : '' ) . $fileName;
				}
			}
			if ($ue) {
				$this->succ(['src' => $sav]);
			} else {
				$this->succ($sav);
			}
		} else {
			$this->fail($file->getError());
		}
	}

	public function up(Request $request) {
		$file = $request->file('file');
		if ($file) {
			return $this->upload_file($request);
		} else {
			return $this->upload_base64($request);
		}
	}

	public function upfull(Request $request) {
		$file = $request->file('file');
		if ($file) {
			return $this->upload_file($request, 1);
		} else {
			return $this->upload_base64($request, 1);
		}
	}

	public function up_ue(Request $request) {
		$file = $request->file('file');
		if ($file) {
			return $this->upload_file($request, 1, 1);
		} else {
			return $this->upload_base64($request, 1, 1);
		}
	}

	public function upload_base64(Request $request, $full = 0, $ue = 0) {
		$token = $this->token;
		if (!$request->has('file')) {
			$this->fail('需要file字段提交base64');
		}
		$image = input('post.file');
		if (!$image) {
			return [
				'code' => 404,
				'data' => '没有找到文件'
			];
		}
		$savePath = date('Ymd', time()) . '/';
		$file_name = md5(time() . microtime());

		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $image, $result)) {
			$proc = \app\index\model\ProjectModel::api_find_token($token);
			if (!$proc) {
				$this->fail('项目不可用');
			}
			$oss = new \OSS\AliyunOSS($proc);
			$ext = explode(',', $proc['ext']);
			$type = $result[2];
			if (!in_array($type, $ext)) {
				$_message['message'] = '仅允许:' . $proc['ext'];
				return $_message;
			}
			$pic_path = 'upload/' . $savePath;
			$file_path = $pic_path . $file_name . "." . $type;
			if (!file_exists($pic_path)) {
				mkdir($pic_path);
			}
			$file_size = file_put_contents($file_path, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image)));
			if (!$file_path || $file_size > 10 * 1024 * 1024) {
				unlink($pic_path);
				return [
					'code' => 500,
					'data' => '图片保存失败'
				];
			}
			$fileName = $proc['name'] . '/' . $savePath . $file_name . "." . $type;
			$type = explode(',', $proc['type']);
			if (in_array('oss', $type)) {
				$oss->uploadFile($proc['bucket'], $fileName, $file_path);
				if ($proc['main_type'] == 'oss') {
					$sav = ($full ? $proc['url'] . '/' : '') . $fileName;
				}
			}
			if (!in_array('local', $type)) {
				unlink($file_path);
			} else {
				if ($proc['main_type'] == 'local') {
					$sav = ($full ? $proc['url'] . '/' : '') . $fileName;
				}
			}
			if ($ue) {
				$this->succ(['src' => $sav]);
			} else {
				$this->succ($sav);
			}
		} else {
			return [
				'code' => 507,
				'data' => '图片格式编码错误'
			];
		}
	}

	public function succ($data = '成功', $code = 0) {
		echo json_encode([
			'code' => $code,
			'data' => $data,
				], 320);
		exit(0);
	}

	public function fail($data = '失败', $code = 400) {
		$this->succ($data, $code);
	}

}
