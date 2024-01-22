<?php
namespace App\Model\Trs\Sensor2201db;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class T_pelet extends Model {

	use SoftDeletes;

	protected $connection = 'sensor2201db';
	public $incrementing = true;
	public $timestamps = false;
	protected $hidden = [];
	protected $dates = ['deleted_at'];
	protected $table = 't_pelet';
	protected $primaryKey = "id";
	protected $fillable = [
		'id',
		'pos',
		'area',
		'kg',
		'waktu',
		'plant',
		'created_by',
		'created_at',
		'updated_at',
		'updated_by',
		'deleted_at',
		'no_crate',
	];

	public function rel_created_by() {
		return $this->belongsTo('App\Model\Sys\Syuser', 'created_by');
	}

}
