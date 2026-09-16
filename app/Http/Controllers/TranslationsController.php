<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use App\Models\LanguageLine;
use Datatables;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TranslationsController extends Controller
{
    protected $transDB;
    protected $fallback;
    protected $from;
    protected $to;
    protected $group;

    public function __construct(Request $request)
    {
        $this->group = $request->group ? $request->group : '';
        $this->fallback = $this->getLocaleArray(config('app.fallback_locale'));
        $trans = LanguageLine::all();

        foreach($trans as $key=>$item) {
            $this->transDB[$item->group . '.' . $item->key] = $item->text;
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('admin.translations.index')) {
            return redirect_no_permission();
        }

        $groups = self::getGroups();
        return view('translations.index', ['groups' => $groups]);
    }

    static public function getGroups()
    {
        $groups = [];
        foreach(scandir(resource_path('lang/en-GB')) as $group) {
            if (in_array($group, ['.','..'])) {
                continue;
            }
            if (is_dir(resource_path('lang/en-GB'). '/' . $group)) {
                foreach(scandir(resource_path('lang/en-GB'). '/' . $group) as $subGroup) {
                    if (in_array($subGroup, ['.','..'])) {
                        continue;
                    }
                    $groups[] = $group . '/' . $subGroup;
                }
            }
            else {
                $groups[] = str_replace('.php', '', $group);
            }
        }
        return $groups;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.translations.index')) {
            return redirect_no_permission();
        }

        if (!empty($request->search) && strlen($request->search) > 5) {
            $langPath = resource_path('lang/en-GB');
            $localLangPath = resource_path('lang' . DIRECTORY_SEPARATOR . config('app.locale'));

            $response = [];

            $lang = $this->getLangFromDir($langPath);
            $localLang = $this->getLangFromDir($localLangPath);

            $lang = array_merge($lang, $localLang);
            $line = Arr::get($lang, $request->search);

            if ($line != $request->search) {
                if (is_array($line)) {
                    $translations = $this->getItemsRecursive($line);

                    foreach($translations as $id=>$text) {
                        $response[] = ['id' => $request->search.'.'.$id, 'text' => $id, 'translate' => $text];
                    }
                }
                else {
                    $response[] = ['id' => $request->search, 'text' => $request->search, 'translate' => $line];
                }
            }
            else {
                $response[] = ['id' => $request->search, 'text' => $request->search];
            }

            return response()->json(['results' => $response, 'status' => Arr::get($lang, $request->search)]);
        }
    }

    /**
     * @param $arr
     * @param array $prepareArr
     * @param string $subKey
     * @return array
     */
    private function getItemsRecursive($arr, $prepareArr = [], $subKey = '')
    {
        foreach($arr as $key=>$item) {
            if (is_array($item)) {
                $subKey = !empty($subKey) ? $subKey . '.' . $key : $key;
                $subArr = $this->getItemsRecursive($item, $prepareArr, $subKey);
                $prepareArr = array_merge($prepareArr, $subArr);
            }
            else {
                $prepareArr[$key] = $item;
            }
        }
        return $prepareArr;
    }

    /**
     * @param $arr
     * @param $key
     * @return bool
     */
    private function existRecursive($arr, $key)
    {
        if (!Arr::exists($arr, $key)) {
            if (preg_match('#\.#', $key)) {
                $key = preg_replace('#\.[[alnum]]$#', '', $key);
                return $this->existRecursive($arr, $key);
            }
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * @param $path
     * @param string $dir
     * @return array
     */
    private function getLangFromDir($path, $dir = '', $locale = false)
    {
        $lang = [];
        if (file_exists(parse_path($path, 'real'))) {
            $group = $this->group;
//            if ($this->group != '') {
                $group .= $dir;
//            }

            if (is_file(parse_path($path, 'real'))) {
//                $lang[$this->group . $dir] = include($path); //  $dir . basename(str_replace('.php', '', $path))
                $lang[$group] = include($path); //  $dir . basename(str_replace('.php', '', $path))
            }
            else {
                $filesList = scandir(parse_path($path, 'real'));
                foreach ($filesList as $key => $value) {
                    if (in_array($value, ['.', '..'])) {
                        continue;
                    }

                    $iPath = $path . DIRECTORY_SEPARATOR . $value;

//                    if ($this->group == '') {
//                        $explodePath =
//                            explode(
//                                parse_path('lang'  . DIRECTORY_SEPARATOR .  $locale . $dir, 'real'),
//                                parse_path($iPath, 'real')
//                            );
//                        $group .= !empty($explodePath[1]) ? preg_replace('#(^\\\\|^/|.php$)#', '', $explodePath[1]) : '';
//                        $group .= str_replace('.php', '', $value);
//                    }

                    if (is_dir($iPath)) {
                        $dirFiles = $this->getLangFromDir($iPath,
                                basename(str_replace('.php', '', $value)) . '/',
                                $locale
                            );
                        $lang = array_merge($lang, $dirFiles);
                    }
                    else {
//                        $lang[$this->group . $dir] = include($iPath); //  $dir . basename(str_replace('.php', '', $value))
                        if ($this->group == '') {
                            $lang[$group . str_replace('.php', '', $value)] = include($iPath);
                        }
                        else {
                            $lang[$group] = include($iPath);
                        }
                    }
                }
            }
        }
        return $lang;
    }

    /**
     * @param $locale
     * @return array
     */
    private function getLocaleArray($locale) {
        $this->group = (int)request('allFiles') === 1 ? '' : $this->group;
        $group = !empty($this->group) ? DIRECTORY_SEPARATOR . $this->group . '.php' : '';
        return $this->getLangFromDir(resource_path('lang/' . $locale . $group),'', $locale);
    }

    /**
     * @param $data
     * @param $lang
     * @param $column
     * @param $locale
     * @return mixed
     */
    private function prepareCompareLocales($data, $lang, $column, $locale) {
        foreach($data as $key => $item) {
            $line = Arr::get($lang, $item['key']);

            if ($column == 'to') {
                $data[$key][$column] = isset($this->transDB[$item['key']][$locale]) ? $this->transDB[$item['key']][$locale] : $line;
                $data[$key]['isTranslate'] = isset($this->transDB[$item['key']][$locale]);
            }
            else {
                $data[$key][$column] = null !== $line ? $line : $data[$key][$column];
            }
        }
        return $data;
    }

    /**
     * @param $fallback
     * @param array $data
     * @param array $subKey
     * @return array
     */
    private function getKeysOriginLocale($fallback, $data = [], $subKey = []) {
        foreach ($fallback as $key => $value) {
            $newKey = $subKey;
            $newKey[] = $key;

            if (is_array($value)) {
                $data = $this->getKeysOriginLocale($value, $data, $newKey);
            }
            else {
                $data[] = [
                    'actions' => '',
                    'key' => implode('.', $newKey),
                    'from' => $value,
                    'to' => $value,
                    'isTranslate' => false,
                ];
            }
        }
        return $data;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function getList(Request $request) {
        if ( $request->ajax() ) {
            if ($request->from) {
                $from = $this->getLocaleArray($request->from);
            }

            if ($request->to) {
                $to = $this->getLocaleArray($request->to);
            }

            if (isset($from) && isset($to)) {
                $data = $this->getKeysOriginLocale($this->fallback);

                if ($data) {
                    $data = $this->prepareCompareLocales($data, $from, 'from', $request->from);
                    $data = $this->prepareCompareLocales($data, $to, 'to', $request->to);
                }

                return Datatables::collection(collect($data))->make(true);
            }
            else {
                return false;
            }
        }
    }

    /**
     * @param Request $request
     * @return LanguageLine
     * @throws \Exception
     */
    public function save(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.translations.edit')) {
            return redirect_no_permission();
        }
        $attributes = $request->notUsed;
        $key = explode('.', $request->key);
        $group = $key[0];
        unset($key[0]);
        $key = implode('.', $key);
        $fromFiles = [];
        $trans = [];

        foreach($request->code as $id => $locale) {
            $trans[$locale] = $request->value[$id];
        }

        $lang = LanguageLine::where('group', $group)->where('key', $key)->first();

        if (!$lang) {
            $lang = new LanguageLine();
            $lang->group = $group;
            $lang->key = $key;
            $lanText = [];
        }
        else {
            $lanText = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($lang->text), true);
            if (null === $lanText) {
                $lanText = [];
            }
        }

        $lanText = array_merge($lanText, $trans);

        foreach(config('app.locales') as $locale) {
            $localeGroupLang = $this->getLocaleArray($locale['code']);

            $fromFiles[$locale['code']] = Arr::get($localeGroupLang, $request->key);
        }
        if (count($request->code) > 1) {
            foreach ($lanText as $id => $item) {
                if (empty($item) || $fromFiles[$id] == $lanText[$id]) {
                    unset($lanText[$id]);
                }
            }
        }

        if (count($lanText) > 0) {
            $lang->text = $lanText;
            $lang->save();
        }
        else {
            $lang->delete();
        }

        if (!empty($attributes)) {
            custom_log('translations', trans('translations.set_log', ['key' => $group.'.'.$key]), $attributes);
        }

        return $lang;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFromLocale(Request $request, $getText = false) {
        $code = $getText ? $request->from : $request->code;
        $eplKey = explode('.', $request->key);
        unset($eplKey[0]);
        $key = $getText ? $this->group . '.' . implode('.', $eplKey) : $request->key;
        // \Log::info([$request->all(), $code, $key]);
        $locale = app()->getLocale();
        app()->setLocale($code);
        $text = trans($key);
        app()->setLocale($locale);
        if ($getText) {
            return $text;
        }
        return response()->json(['text' => $text]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request) {
        $lang = $this->getByKey($request->key);
        if (empty($lang)) {
            $lang = new \stdClass();
        }
        else {
            $lang = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($lang));
        }
        $lang->file = [];
        foreach(config('app.locales') as $locale) {
            $localeGroupLang = $this->getLocaleArray($locale['code']);
            $lang->file[$locale['code']] = Arr::get($localeGroupLang, $request->key);
        }
        return response()->json($lang);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request) {
        if (!auth()->user()->hasPermission('admin.translations.destroy')) {
            return redirect_no_permission();
        }

        $lang = $this->getByKey($request->key);
        $text = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($lang->text), true);
        unset($text[$request->code]);
        if (count($text) === 0) {
            $lang->delete();
        }
        else {
            $lang->text = $text;
            $lang->save();
        }
        return response()->json(['lang' => $lang, 'origin' => $this->getFromLocale($request, true)]);
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getByKey($key) {
        $key = explode('.', $key);
        $group = $key[0];
        unset($key[0]);
        $key = implode('.', $key);
        $lang = LanguageLine::where('group', $group)->where('key', $key)->first();
        return $lang;
    }

    public function clearCache() {
        if (!auth()->user()->hasPermission('admin.translations.destroy')) {
            return redirect_no_permission();
        }

        if (config('cache.default') == 'database') {
            $cache =\DB::table('cache')->where('key', 'LIKE', 'spatie.translation-loader.%')->get();
            foreach ($cache as $item) {
                Cache::forget($item->key);
            }
        }
        else {
            clear_cache('cache');
        }
    }

    public function clearTranslations() {
        if (!auth()->user()->hasPermission('admin.translations.destroy')) {
            return redirect_no_permission();
        }
        $this->clearCache();
        \DB::table('language_lines')->truncate();
    }

    static public function export(Request $request) {
        // http://eto.d/translations/export?from=en-GB&to=hu-HU&ext=csv

        $request->from = !empty($request->from) ? $request->from : '';
        $request->to = !empty($request->to) ? $request->to : '';

        if (empty($request->from) || empty($request->to)) {
            return 'No access';
        }

        $data  = [];
        $groups = (new self($request))->getGroups();
        $fileExt = !empty($request->ext) && in_array($request->ext, ['xlsx', 'xls', 'csv']) ? $request->ext : 'xls';
        $fileName = 'Translations_'. $request->from .'_'. $request->to .'_'. \Carbon\Carbon::now()->format('Y-m-d_H:i:s');

        foreach ($groups as $group) {
            $request->group = str_replace('.php', '', $group);
            $that = new self($request);

            if ($request->from) {
                $from = $that->getLocaleArray($request->from);
            }
            if ($request->to) {
                $to = $that->getLocaleArray($request->to);
            }

            if (isset($from) && isset($to)) {
                $data2 = $that->getKeysOriginLocale($that->fallback);
                if ($data2) {
                    $data2 = $that->prepareCompareLocales($data2, $from, 'from', $request->from);
                    $data2 = $that->prepareCompareLocales($data2, $to, 'to', $request->to);
                }

                foreach ($data2 as $key => $value) {
                    $data[] = [
                        'key' => $value['key'],
                        'from' => $value['from'],
                        'to' => $value['to'],
                    ];
                }
            }
        }

        return \Excel::create($fileName, function($excel) use ($data) {
            $excel->sheet('Translations', function($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download($fileExt);
    }
}
