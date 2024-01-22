<?php
namespace App\Http\Controllers\Trs\Local;

use App\Http\Controllers\Controller;
use App\Model\Trs\Sensor2201db\T_pelet;
use App\Model\Trs\Imsdb\Prov01;
use App\Model\Trs\Zrfc\T_parlks;
use Auth;
use DB;
use App\Sf;
use Illuminate\Http\Request;

class NpeletController extends Controller
{

	public function index(Request $request)
	{
		if (!$plant = Sf::isPlant()) {
			return Sf::selectPlant();
		}

		Sf::log("trs_local_npelet", "NpeletController@" . __FUNCTION__, "Open Page  ", "link");

		return view('trs.local.npelet.npelet_frm', compact(['request', 'plant']));
	}

	public function getList(Request $request)
	{
		if (!Sf::allowed('TRS_LOCAL_NPELET_R')) {
			return response()->json(Sf::reason(), 401);
		}
		$request->q = str_replace(" ", "%", $request->q);
		$data = T_pelet::where(function ($q) use ($request) {
			$q->orWhere('id', 'like', "%" . @$request->q . "%");
			$q->orWhere('pos', 'like', "%" . @$request->q . "%");
			$q->orWhere('area', 'like', "%" . @$request->q . "%");
			$q->orWhere('kg', 'like', "%" . @$request->q . "%");
			$q->orWhere('waktu', 'like', "%" . @$request->q . "%");
			$q->orWhere('plant', 'like', "%" . @$request->q . "%");
			$q->orWhere('created_by', 'like', "%" . @$request->q . "%");
			$q->orWhere('created_at', 'like', "%" . @$request->q . "%");
			$q->orWhere('updated_at', 'like', "%" . @$request->q . "%");
			$q->orWhere('updated_by', 'like', "%" . @$request->q . "%");
			$q->orWhere('deleted_at', 'like', "%" . @$request->q . "%");
		})
			->whereDate('waktu', '>=', @$request->date1)
			->whereDate('waktu', '<=', @$request->date2)
			//->where('plant',$request->plant)
			->orderBy(isset($request->order_by) ? substr($request->order_by, 1) : 'id', substr(@$request->order_by, 0, 1) == '-' ? 'desc' : 'asc');
		if ($request->trash == 1) {
			$data = $data->onlyTrashed();
		}
		$data = $data->paginate(isset($request->limit) ? $request->limit : 10);
		$data->getCollection()->transform(function ($value) {
			//isikan transformasi disini
			$value->token = Sf::encrypt($value->id);
			return $value;
		});
		return response()->json(compact(['data']));
	}

	public function getLookup(Request $request)
	{
		$request->q = str_replace(" ", "%", $request->q);
		$data = T_pelet::where(function ($q) use ($request) {
			$q->orWhere('id', 'like', "%" . @$request->q . "%");
			$q->orWhere('pos', 'like', "%" . @$request->q . "%");
			$q->orWhere('area', 'like', "%" . @$request->q . "%");
			$q->orWhere('kg', 'like', "%" . @$request->q . "%");
			$q->orWhere('waktu', 'like', "%" . @$request->q . "%");
			$q->orWhere('plant', 'like', "%" . @$request->q . "%");
			$q->orWhere('created_by', 'like', "%" . @$request->q . "%");
			$q->orWhere('created_at', 'like', "%" . @$request->q . "%");
			$q->orWhere('updated_at', 'like', "%" . @$request->q . "%");
			$q->orWhere('updated_by', 'like', "%" . @$request->q . "%");
			$q->orWhere('deleted_at', 'like', "%" . @$request->q . "%");
		})
			//->where('plant',$request->plant)
			->orderBy(isset($request->order_by) ? substr($request->order_by, 1) : 'id', substr(@$request->order_by, 0, 1) == '-' ? 'desc' : 'asc');
		$data = $data->paginate(isset($request->limit) ? $request->limit : 10);
		return view('sys.system.dialog.sflookup', compact(['data', 'request']));
	}

	public function store(Request $request)
	{
		$req = json_decode(request()->getContent());
		$h = $req->h;
		$f = $req->f;

		try {
			$arr = array_merge((array) $h, ['plant' => $f->plant, 'updated_at' => date('Y-m-d H:i:s')]);
			if ($f->crud == 'c') {
				if (!Sf::allowed('TRS_LOCAL_NPELET_C')) {
					return response()->json(Sf::reason(), 401);
				}
				$data = new Npelet();
				$arr = array_merge($arr, ['created_by' => Auth::user()->userid, 'created_at' => date('Y-m-d H:i:s')]);
				$data->create($arr);
				$id = DB::getPdo()->lastInsertId();
				$storedata = $this->storedata($id, $arr, $f, $h);
				Sf::log("trs_local_npelet", $id, "Create Monitoring Pelet (npelet) id : " . $id, "create");
				return response()->json($storedata);
				// return response()->json('created');
			} else {
				if (!Sf::allowed('TRS_LOCAL_NPELET_U')) {
					return response()->json(Sf::reason(), 401);
				}
				$id = Sf::decrypt($h->token);
				$data = T_pelet::find($id);
				if ($data === null) {
					return response()->json("error token", 400);
				}
				$data->update($arr);
				$id = $data->id;
				Sf::log("trs_local_npelet", $id, "Update Monitoring Pelet (npelet) id : " . $id, "update");
				return response()->json('updated');
			}


		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 500);
		}
	}
	public function storedata($id, $arr, $f, $h)
	{
		$parlks = T_parlks::where('ISAKTIF', 1)->where('LKS', $f->plant)->first();
		if ($parlks == false) {
			return response()->json("T_parlks not found", 500);
		}
		if (!isset($f->BARCODE)) {
			$newNumber = Sf::autonumber(date("ym") . $parlks->PROV_AUTONUMBERCHAR, 12, 'imsdb', 'NO_CRATE', 'PROV01', "");
			$arr['BARCODE'] = $newNumber;
		} else {
			$newNumber = Sf::autonumber(trim($f->BARCODE) . "-", 14, 'imsdb', 'NO_CRATE', 'PROV01', "");
		}
		// dd($arr);
		$data = new Prov01();
		$arr = array_merge($arr, [
			'BARCODE' => isset($f->BARCODE) ? $f->BARCODE : $newNumber,
			'NO_CRATE' => $newNumber,
			'ID_MESIN' => 'PLT-0003',
			'ID_STATION' => 'PLT',
			'ID_ITEM' => 'WP8MM',
			'SAP_SLOC' => '0006',
			// 'OTHER_QTY' => isset($arr->kg) ? $arr->kg : 0,
			'OTHER_QTY' => isset($arr['kg']) ? $arr['kg'] : 0,
			'OTHER_UNIT' => 'KG',
			'TGL_PROD' => date("Y-m-d"),
			'TGL_POSTING' => date("Y-m-d"),
			'SHIFT' => 'N',
			'INOUT' => 'OUT',
			'TRS_TYPE' => 'PROD',
			'ORDER_TYPE' => 'PO',
			'PLANT' => '2201',
			'LKS' => '9113',
			'LOG_APP' => 'AUTO',
			'LOG_USER' => Auth::user()->userid,
			'LOG_DATE' => date('Y-m-d H:i:s'),
		]);
		// return $arr;
		$data->create($arr);
		return ['status' => 'success', 'data' => $arr['NO_CRATE']];
		// return ['status' => 'success', 'data' => $newNumber];

	}

	public function getData()
	{
		try {
			$startDate = now()->subWeek(); 
			$endDate = now();

			$lastWeekData = T_pelet::whereBetween('waktu', [$startDate, $endDate])
				->whereNull('no_crate')
				->get();
			foreach ($lastWeekData as $record) {
				$id = $record->id;
				$arr = $record->toArray();
				$f = (object) ['plant' => $arr['plant'], 'crud' => 'c']; 
				$h = (object) ['token' => Sf::encrypt($id)]; 
				// dd($arr);

				$storedata = $this->storedata($id, $arr, $f, $h);

				if ($storedata['status'] == 'success') {
					$record->update(['no_crate' => $storedata['data']]);
					Sf::log("trs_local_npelet", $id, "Update no_crate for Monitoring Pelet (npelet) id : " . $id, "update");
				}
			}

			return response()->json('Processing complete');
		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 500);
		}
	}

	public function edit($token)
	{
		$id = Sf::decrypt($token);
		$h = T_pelet::where('id', $id)->withTrashed()->first();
		if ($h === null) {
			return response()->json("error token", 400);
		}
		$h->token = $token;
		return response()->json(compact(['h']));
	}

	public function destroy($token, Request $request)
	{
		try {
			$id = Sf::decrypt($token);
			$data = T_pelet::where('id', $id)->withTrashed()->first();
			if ($data === null) {
				return response()->json("error token", 400);
			}
			if ($request->restore == 1) {
				if (!Sf::allowed('TRS_LOCAL_NPELET_S')) {
					return response()->json(Sf::reason(), 401);
				}
				$data->restore();
				Sf::log("trs_local_npelet", $id, "Restore Monitoring Pelet (npelet) id : " . $id, "restore");
				return response()->json('restored');
			} else {
				if (!Sf::allowed('TRS_LOCAL_NPELET_D')) {
					return response()->json(Sf::reason(), 401);
				}
				$data->delete();
				Sf::log("trs_local_npelet", $id, "Delete Monitoring Pelet (npelet) id : " . $id, "delete");
				return response()->json('deleted');
			}
		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 500);
		}
	}
}