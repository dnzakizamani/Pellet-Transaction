@extends('layouts.coloradmin')
<!-- ------------------------------------------------------------------------------- -->
@section('title')Pellet Monitoring @stop
<!-- ------------------------------------------------------------------------------- -->
@section('title-small') @stop
<!-- ------------------------------------------------------------------------------- -->
@section('breadcrumb')
    <span ng-show="f.tab=='list'">Data List</span>
<span ng-show="f.tab=='frm'">Form Entry</span> @stop
<!-- ------------------------------------------------------------------------------- -->
@section('content')
    <div class="panel panel-success">
        <div class="panel-heading">
            @component('layouts.common.coloradmin.panel_button')
            @endcomponent @yield('breadcrumb')
        </div>
        <div class="panel-body">
            <div class="m-b-5 form-inline">
                <div class="pull-right">
                    <div ng-show="f.tab=='list'">
                        @component('layouts.common.coloradmin.guide', ['tag' => 'trs_local_npelet'])
                        @endcomponent
                        <div class="input-group">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm" ng-click="oPrintTable()"><i
                                        class="fa fa fa-print"></i></button>
                                {{-- <button type="button" class="btn btn-success btn-sm"  ng-click="oSearch(1)"><i class="fa fa fa-recycle"></i></button> --}}
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control input-sm" ng-model="f.q" ng-enter="oSearch()"
                                placeholder="Search">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success btn-sm" ng-click="oSearch()"><i
                                        class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div ng-show="f.tab=='frm'">
                        <button type="button" class="btn btn-sm" style="background-color:#8479E1;color:white"
                            ng-click="oPrint()" ng-show="f.crud=='u' && f.trash!=1"><i class="fa fa fa-print"></i>
                            Print</button>
                        <button type="button" class="btn btn-sm btn-success" ng-click="oSave()"
                            ng-show="f.crud=='c' && f.trash!=1"><i class="fa fa-save"></i> Create</button>
                        <button type="button" class="btn btn-sm btn-success" ng-click="oSave()"
                            ng-show="f.crud=='u' && f.trash!=1"><i class="fa fa-save"></i> Update</button>
                        <button type="button" class="btn btn-sm btn-warning" ng-click="oCopy()" ng-show="f.crud=='u'"><i
                                class="fa fa-copy"></i> Copy</button>
                        <button type="button" class="btn btn-sm btn-danger" ng-click="oDel()"
                            ng-show="f.crud=='u'&& f.trash!=1"><i class="fa fa-trash"></i> Delete</button>
                        <button type="button" class="btn btn-sm btn-warning" ng-click="oRestore()"
                            ng-show="f.crud=='u' && f.trash==1"><i class="fa fa-recycle"></i> Restore</button>
                        <button type="button" class="btn btn-sm btn-info" ng-click="oLog()" ng-show="f.crud=='u'"><i
                                class="fa fa-clock-o"></i> Log</button>
                        <span ng-if="f.crud!='c'"> @component('layouts.common.coloradmin.chat', ['route' => 'trs_local_npelet', 'id' => 'h.id'])
                            @endcomponent </span>
                    </div>
                </div>
                {{-- <button type="button" class="btn btn-sm btn-inverse" ng-click="oNew()" ng-attr-title="Buat Baru" ng-show="f.tab=='list' && f.trash!=1"><i class="fa fa-plus"></i> New</button> --}}
                <button type="button" class="btn btn-sm btn-inverse" ng-click="f.tab='list'"
                    ng-attr-title="Kembali ke Halaman Awal" ng-show="f.tab=='frm'"><i class="fa fa-arrow-left"></i>
                    Back</button>
            </div>
            <br>
            <div ng-show="f.tab=='list'">
                <div class="alert alert-warning" ng-show="f.trash==1"><i class="fa fa-warning fa-2x"></i> This is deleted
                    item<br>Trashed</div>
                <div class="row ">
                    <div class="col-sm-4" style="padding: 10px;">
                        <label>Date Start</label>
                        <input type="date" ng-model="f.date1" class="form-control input-sm">
                    </div>
                    <div class="col-sm-4" style="padding: 10px;">
                        <label>Date To</label>
                        <input type="date" ng-model="f.date2" class="form-control input-sm">
                    </div>
                    <div class="col-sm-4" style="padding: 10px;">
                        <label class="m-b-0">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-success btn-block m-t-0" ng-click="oSearch()"><i
                                class="fa fa-refresh"></i> Refresh</button>
                    </div>
                </div>
                <hr />
                <div id="div1" class="table-responsive">
                    <table ng-table="tableList" show-filter="false" class="table table-condensed table-bordered tbl-list"
                        style="white-space: nowrap;">
                        <tr ng-repeat="v in $data" {{-- class="pointer" ng-click="oShow(v.token)" --}}>
                            <td title="'Id'" filter="{id: 'text'}" sortable="'id'">@{{ $index + 1 }}</td>
                            {{-- <td title="'Pos'" filter="{pos: 'text'}" sortable="'pos'">@{{ v.pos }}</td>
                            <td title="'Area'" filter="{area: 'text'}" sortable="'area'">@{{ v.area }}</td> --}}
                            <td title="'Berat'" filter="{kg: 'text'}" sortable="'kg'">@{{ v.kg }} Kg</td>
                            <td title="'Tanggal'" filter="{tanggal: 'text'}" sortable="'waktu'">@{{ v.waktu.split(' ')[0] }}</td>
                            <td title="'Jam'" filter="{jam: 'text'}" sortable="'waktu'">@{{ v.waktu.split(' ')[1] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div ng-show="f.tab=='frm'">
                <form action="#" name="frm" id="frm">
                    <div class="row">
                        <div class="col-sm-4">
                            <label title='id'>Id</label>
                            <input type="text" ng-model="h.id" id="h_id" class="form-control input-sm" readonly
                                maxlength="" ng-readonly="f.crud!='c' || true " placeholder="auto">
                            <label title='pos'>Pos</label>
                            <input type="text" ng-model="h.pos" id="h_pos" class="form-control input-sm"
                                maxlength="11">
                        </div>
                        <div class="col-sm-4">
                            <label title='area'>Area</label>
                            <input type="text" ng-model="h.area" id="h_area" class="form-control input-sm"
                                maxlength="11">
                            <label title='kg'>Kg</label>
                            <input type="text" ng-model="h.kg" id="h_kg" class="form-control input-sm"
                                maxlength="">
                        </div>
                        <div class="col-sm-4">
                            <label title='waktu'>Waktu</label>
                            <input type="text" ng-model="h.waktu" id="h_waktu" class="form-control input-sm"
                                maxlength="">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style type="text/css">
        .tbl-list>thead>tr>th {
            background-color: #000000 !important;
            color: white !important;
            padding: 5px 10px;
        }
    </style>
    <script>
        app.controller('mainCtrl', ['$scope', '$http', 'NgTableParams', 'SfService', 'FileUploader', function($scope, $http,
            NgTableParams, SfService, FileUploader) {
            SfService.setUrl("{{ url('trs_local_npelet') }}");
            $scope.f = {
                crud: 'c',
                tab: 'list',
                trash: 0,
                userid: "{{ Auth::user()->userid }}",
                plant: "{{ Session::get('plant') }}",
                date1: moment().startOf('month').toDate(),
                date2: moment().endOf('month').toDate(),
                req_date1: moment().subtract(3, 'd').toDate(),
                req_date2: moment().toDate(),
            };
            $scope.h = {};
            $scope.m = [];

            var uploader = $scope.uploader = new FileUploader({
                url: "{{ url('upload_file') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                onBeforeUploadItem: function(item) {
                    //s pattern : t : text, i : image,a : audio, v : video, p : application, x : all mime
                    item.formData = [{
                        id: $scope.h.id,
                        path: 'trs_local_npelet',
                        s: 'i',
                        userid: $scope.f.userid,
                        plant: $scope.f.plant
                    }];
                },
                onSuccessItem: function(fileItem, response, status, headers) {
                    $scope.oGallery();
                }
            });

            $scope.oGallery = function() {
                SfGetMediaList('trs_local_npelet/' + $scope.h.id, function(jdata) {
                    $scope.m = jdata.files;
                    $scope.$apply();
                });
            }

            $scope.oNew = function() {
                $scope.f.tab = 'frm';
                $scope.f.crud = 'c';
                $scope.h = {};
                $scope.m = [];
                SfFormNew("#frm");
            }

            $scope.oCopy = function() {
                $scope.f.crud = 'c';
                $scope.h.id = null;
            }


            $scope.oSearch = function(trash, order_by) {
                $scope.f.tab = "list";
                $scope.f.trash = trash;
                $scope.tableList = new NgTableParams({}, {
                    getData: function($defer, params) {
                        var $btn = $('button').button('loading');
                        return $http.get(SfService.getUrl("_list"), {
                            params: {
                                page: $scope.tableList.page(),
                                limit: $scope.tableList.count(),
                                order_by: $scope.tableList.orderBy(),
                                q: $scope.f.q,
                                trash: $scope.f.trash,
                                plant: $scope.f.plant,
                                userid: $scope.f.userid,
                                date1: $scope.f.date1,
                                date2: $scope.f.date2,
                            }
                        }).then(function(jdata) {
                            $btn.button('reset');
                            $scope.tableList.total(jdata.data.data.total);
                            return jdata.data.data.data;
                        }, function(error) {
                            $btn.button('reset');
                            swal('', error.data, 'error');
                        });
                    }
                });
            }

            $scope.oSave = function() {
                SfService.save("#frm", SfService.getUrl(), {
                    h: $scope.h,
                    f: $scope.f
                }, function(jdata) {
                    $scope.oSearch();
                });
            }

            $scope.oShow = function(token) {
                SfService.show(SfService.getUrl("/" + encodeURI(token) + "/edit"), {}, function(jdata) {
                    $scope.oNew();
                    $scope.h = jdata.data.h;
                    $scope.f.crud = 'u';
                    $scope.oGallery();
                    if (chatCtrl() != undefined) {
                        chatCtrl().listChat();
                    }
                });
            }

            $scope.oDel = function(token, isRestore) {
                if (token == undefined) {
                    var token = $scope.h.token;
                }
                SfService.delete(SfService.getUrl("/" + encodeURI(token)), {
                    restore: isRestore
                }, function(jdata) {
                    $scope.oSearch();
                });
            }

            $scope.oRestore = function(id) {
                $scope.oDel(id, 1);
            }

            $scope.oLookup = function(id, selector, obj) {
                switch (id) {
                    /*case 'parent':
                        SfLookup(SfService.getUrl("_lookup"), function(id, name, jsondata) {
                            $("#" + selector).val(id).trigger('input');;
                        });
                        break;*/
                    default:
                        swal('Sorry', 'Under construction', 'error');
                        break;
                }
            }

            $scope.oLog = function() {
                SfLog('trs_local_npelet', $scope.h.id);
            }

            $scope.oPrint = function() {
                window.open(SfService.getUrl('_print') + "/" + '?token=' + $scope.h.token);
            }

            $scope.oSearch();
        }]);
    </script>
@endsection
