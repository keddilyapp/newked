<?php

namespace Modules\CountryManage\Http\Controllers\Tenant\Admin;

use App\Helpers\FlashMsg;
use App\Helpers\SanitizeInput;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CountryManage\Entities\Country;
use Illuminate\Support\Facades\Session;
use Modules\CountryManage\Entities\State;
use Illuminate\Support\Str;
use Modules\CountryManage\Http\Requests\UpdateStateRequest;
use Modules\ShippingModule\Entities\ZoneRegion;

class StateController extends Controller
{
    private const BASE_PATH = 'countrymanage::tenant.admin.';

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:state-list|state-create|state-edit|state-delete', ['only', ['index']]);
        $this->middleware('permission:state-create', ['only', ['store']]);
        $this->middleware('permission:state-edit', ['only', ['update']]);
        $this->middleware('permission:state-delete', ['only', ['destroy', 'bulk_action']]);
        $this->middleware('permission:state-csv-file-import', ['only', ['import_settings']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all_countries = Country::all();
        $all_states = State::with('country')->paginate(20);
        return view(self::BASE_PATH.'all-state', compact('all_countries', 'all_states'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $state = State::create([
            'name' => SanitizeInput::esc_html($request->name),
            'country_id' => $request->country_id,
            'status' => $request->status,
        ]);

        return $state->id
            ? back()->with(FlashMsg::create_succeed('State'))
            : back()->with(FlashMsg::create_failed('State'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country\State  $state
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStateRequest $request, State $state)
    {
        $updated = State::findOrFail($request->id)->update([
            'name' => SanitizeInput::esc_html($request->name),
            'country_id' => $request->country_id,
            'status' => $request->status,
        ]);

        return $updated
            ? back()->with(FlashMsg::create_succeed('State'))
            : back()->with(FlashMsg::create_failed('State'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(State $item)
    {
        State::findOrFail($id)->delete();
        return redirect()->back()->with(FlashMsg::create_succeed('City Successfully Deleted'));


        return $item->delete()
            ? back()->with(FlashMsg::delete_succeed('State'))
            : back()->with(FlashMsg::delete_failed('State'));
    }

    public function bulk_action(Request $request)
    {
        $deleted = State::whereIn('id', $request->ids)->delete();
        if ($deleted) {
            return 'ok';
        }
    }

    public function getStateByCountry(Request $request)
    {
        $request->validate(['id' => 'required|exists:countries']);
        return State::select('id', 'name')
            ->where('country_id', $request->id)
            ->where('status', 'publish')
            ->get();
    }
    public function getMultipleStateByCountry(Request $request)
    {
        $request->validate(['id' => 'required']);

        return State::select('id', 'name')
            ->whereIn('country_id', $request->id)
            ->where('status', 'publish')
            ->get();
    }

    public function statesByCountryId(Request $request)
    {
        $states = State::where('country_id', $request->country_id)->get()->pluck('name', 'id');

        return response()->json(['status' => 'success', 'states' => $states]);
    }

    public function import_settings()
    {
        return view(self::BASE_PATH.'import-state');
    }

    //state import
    public function update_import_settings(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:150000'
        ]);

        //: work on file mapping
        if ($request->hasFile('csv_file')) {
            $file = $request->csv_file;
            $extenstion = $file->getClientOriginalExtension();
            if ($extenstion == 'csv') {
                //copy file to temp folder

                $old_file = Session::get('import_csv_file_name');
                if (file_exists('assets/uploads/import/' . $old_file)) {
                    @unlink('assets/uploads/import/' . $old_file);
                }
                $file_name_with_ext = $file->getClientOriginalName();

                $file_name = pathinfo($file_name_with_ext, PATHINFO_FILENAME);
                $file_name = strtolower(Str::slug($file_name));

                $file_tmp_name = $file_name . time() . '.' . $extenstion;
                $file->move('assets/uploads/import', $file_tmp_name);

                $data = array_map('str_getcsv', file('assets/uploads/import/' . $file_tmp_name));
                $csv_data = array_slice($data, 0, 1);
                //dd($csv_data, $file_tmp_name);
                Session::put('import_csv_file_name', $file_tmp_name);

                return view(self::BASE_PATH.'import-state', [
                    'import_data' => $csv_data,
                ]);
            }

        }
        return back()->with(FlashMsg::explain('danger', 'Something went wrong try again!'));
    }

    public function import_to_database_settings(Request $request)
    {
        $request->validate([
            'country_id' => 'required',
            'name' => 'required',
            'status' => 'required'
        ]);

        $file_tmp_name = Session::get('import_csv_file_name');
        $data = array_map('str_getcsv', file('assets/uploads/import/' . $file_tmp_name));

        $csv_data = current(array_slice($data, 0, 1));
        $csv_data = array_map(function ($item) {
            return trim($item);
        }, $csv_data);

        $imported_states = 0;
        $x = 0;
        $state = array_search($request->name, $csv_data, true);
        foreach ($data as $index => $item) {
            if($x == 0){
                $x++;
                continue ;
            }
            if ($index === 0) {
                continue;
            }
            if (empty($item[$state])){
                continue;
            }

            $find_state = State::where('name', $item[$state])->where('country_id', $request->country_id)->count();

            if ($find_state < 1) {
                $state_data = [
                    'name' => trim($item[$state]) ?? '',
                    'country_id' => $request->country_id,
                    'timezone' => $request->timezone,
                    'status' => $request->status,
                ];
            }
            if ($find_state < 1) {
                State::create($state_data);
                $imported_states++;
            }
        }

        return redirect()->back()->with(FlashMsg::explain('success', 'States imported successfully'));
    }

}
