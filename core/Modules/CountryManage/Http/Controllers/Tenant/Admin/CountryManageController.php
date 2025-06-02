<?php

namespace Modules\CountryManage\Http\Controllers\Tenant\Admin;

use App\Helpers\FlashMsg;
use App\Helpers\SanitizeInput;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\CountryManage\Entities\Country;
use Modules\CountryManage\Http\Requests\StoreCountryManageRequest;
use Modules\CountryManage\Http\Requests\UpdateCountryManageRequest;

class CountryManageController extends Controller
{
    private const BASE_PATH = 'countrymanage::tenant.admin.';

    public function __construct()
    {
        $this->middleware('auth:admin')->except(['getCountryInfo', 'getStateInfo']);
        $this->middleware('permission:country-list|country-create|country-edit|country-delete', ['only', ['index']]);
        $this->middleware('permission:country-create', ['only', ['store']]);
        $this->middleware('permission:country-edit', ['only', ['update']]);
        $this->middleware('permission:country-delete', ['only', ['destroy', 'bulk_action']]);
        $this->middleware('permission:country-csv-file-import', ['only', ['import_settings']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all_countries = Country::all();
        return view(self::BASE_PATH.'all-country', compact('all_countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCountryManageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCountryManageRequest $request)
    {
        $country = Country::create([
            'name' => SanitizeInput::esc_html($request->name),
            'status' => $request->status,
        ]);

        return $country->id
            ? back()->with(FlashMsg::create_succeed('Country'))
            : back()->with(FlashMsg::create_failed('Country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCountryManageRequest $request)
    {
        $updated = Country::findOrFail($request->id)->update([
            'name' => SanitizeInput::esc_html($request->name),
            'status' => $request->status,
        ]);

        return $updated
            ? back()->with(FlashMsg::update_succeed('Country'))
            : back()->with(FlashMsg::update_failed('Country'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $item)
    {
        try {
            $deleted = $item->delete();
            if ($deleted) {
                return response()->json(['success' => true, 'message' => __('Country deleted successfully.')]);
            } else {
                return response()->json(['success' => false, 'message' => __('Failed to delete country.')]);
            }
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json(['success' => false, 'message' => __('Cannot delete this country because it is referenced by other records.')]);
            } else {
                return response()->json(['success' => false, 'message' => __('An error occurred while deleting the country.')]);
            }
        }
    }

    public function bulk_action(Request $request)
    {
        $deleted = Country::whereIn('id', $request->ids)->delete();
        if ($deleted) {
            return 'ok';
        }
    }

    public function import_settings()
    {
        return view(self::BASE_PATH.'import-country');
    }

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
                $file_name = strtolower(Str::slug($file_name, '-', null));

                $file_tmp_name = $file_name . time() . '.' . $extenstion;
                $file->move('assets/uploads/import', $file_tmp_name);

                $data = array_map('str_getcsv', file('assets/uploads/import/' . $file_tmp_name));
                $csv_data = array_slice($data, 0, 1);

                Session::put('import_csv_file_name', $file_tmp_name);

                return view(self::BASE_PATH.'import-country', [
                    'import_data' => $csv_data,
                ]);
            }

        }

        return back()->with(FlashMsg::explain('danger', 'Something went wrong try again!'));
    }

    public function import_to_database_settings(Request $request)
    {
        $file_tmp_name = Session::get('import_csv_file_name');
        $data = array_map('str_getcsv', file('assets/uploads/import/' . $file_tmp_name));

        $csv_data = current(array_slice($data, 0, 1));
        $csv_data = array_map(function ($item) {
            return trim($item);
        }, $csv_data);

        $imported_countries = 0;
        $x = 0;
        $country = array_search($request->name, $csv_data, true);

        foreach ($data as $index => $item) {
            if($x == 0){
                $x++;
                continue ;
            }
            $find_country = Country::where('name', $item[$country] )->count();

            if ($find_country < 1) {
                $country_data = [
                    'name' => trim($item[$country]) ?? '',
                    'status' => $request->status,
                ];
            }
            if ($find_country < 1) {
                Country::create($country_data);
                $imported_countries++;
            }
        }

        return redirect()->back()->with(FlashMsg::explain('success', 'Countries imported successfully'));

    }

}
