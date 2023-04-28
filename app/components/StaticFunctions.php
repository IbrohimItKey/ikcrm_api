<?php



namespace App\components;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class StaticFunctions {

    public static function fileUploadKartikWithAjax($req, $module_url, $tmpfoldername)
    {
        if ($req->ajax()) {
            $preview = $config = $errors = [];
            $input = 'files';
            if (empty($_FILES[$input])) {
                return [];
            }
            $total = count($_FILES[$input]['name']); // multiple files
            if (!file_exists(public_path('uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername))) {
                $path = public_path('uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername);
                File::makeDirectory($path, $mode = 0777, true, true);
            }

            if (file_exists(public_path('/uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername))) {
                $files_saved_count = count(File::allFiles(public_path('/uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername)));
            }


            $path = public_path('uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername.'/'); // your upload path
            for ($i = 0; $i < $total; $i++) {
                // $filecount = count(File::allFiles(public_path('/uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername)));

                $tmpFilePath = $_FILES[$input]['tmp_name'][$i]; // the temp file path
                $fileName = $_FILES[$input]['name'][$i];
                $fileSize = $_FILES[$input]['size'][$i];

                if ($tmpFilePath != ""){
                    $newFilePath = $path . $fileName;
                    $newFileReName = $path.'/'.$files_saved_count++.'_'.$fileName;
                    if(move_uploaded_file($tmpFilePath, $newFilePath)) {
                        rename($newFilePath, $newFileReName);

                        $pathInfo = pathinfo($path . $newFileReName);
                        $newFileReNamePreView = '/uploads/tmp_files/' . Auth::user()->id.'/'.$tmpfoldername.'/' . $pathInfo['basename'];
                        $fileId = $pathInfo['basename'] . $i; // some unique key to identify the file
                        $preview[] = $newFileReNamePreView;
                        $config[] = [
                            'key' => $fileId,
                            'caption' => $pathInfo['basename'],
                            'size' => $fileSize,
                            'downloadUrl' => $pathInfo['basename'], // the url to download the file
                            'url' => '/'.$module_url.'/'.$tmpfoldername.'/file-delete/'.$pathInfo['basename'], // server api to delete the file based on key
                        ];
                    } else {
                        $errors[] = $fileName;
                    }
                } else {
                    $errors[] = $fileName;
                }
            }
            $out = ['initialPreview' => $preview, 'initialPreviewConfig' => $config, 'initialPreviewAsData' => true];
            if (!empty($errors)) {
                $img = count($errors) === 1 ? 'file "' . $errors[0]  . '" ' : 'files: "' . implode('", "', $errors) . '" ';
                $out['error'] = 'Oh snap! We could not upload the ' . $img . 'now. Please try again later.';
            }
            return $out;
        }
    }






        public static function getMonth($month){
            switch ($month){
                case '1':
                    return 'Январь';
                    break;
                case '2':
                    return 'Февраль';
                    break;
                case '3':
                    return 'Март';
                    break;
                case '4':
                    return 'Апрель';
                    break;
                case '5':
                    return 'Май';
                    break;
                case '6':
                    return 'Июнь';
                    break;
                case '7':
                    return 'Июль';
                    break;
                case '8':
                    return 'Август';
                    break;
                case '9':
                    return 'Сентябрь';
                    break;
                case '10':
                    return 'Октябрь';
                    break;
                case '11':
                    return 'Ноябрь';
                    break;
                case '12':
                    return 'Декабрь';
                    break;
            }
        }


    public static function convertNumberToWord($num = false)
    {
        $num = str_replace(array(',', ' '), '', trim($num));
        if (!$num) {
            return false;
        }
        $num = (int)$num;
        $words = array();
        $list1 = array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять', 'десять', 'eleven',
            'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'
        );
        $list2 = array('', 'десять', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто', 'сто');
        $list3 = array('', 'тысяча', 'миллион', 'миллиард', 'триллион');
        $num_length = strlen($num);
        $levels = (int)(($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int)($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' сто' . ' ' : '');
            $tens = (int)($num_levels[$i] % 100);
            $singles = '';
            if ($tens < 20) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int)($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . (($levels && ( int )($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        return implode(' ', $words);
    }


//    public static function passwordGeneration() {
//        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789#$%&*()-_=+";
//        $pass = array(); //remember to declare $pass as an array
//        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
//        for ($i = 0; $i < 10; $i++) {
//            $n = rand(0, $alphaLength);
//            $pass[] = $alphabet[$n];
//        }
//        return implode($pass); //turn the array into a string
//    }
//    public static function binary_search($list,$item){
//        $low = 0;
//        $high = count($list)-1;
//        while($low <= $high){
//            $mid = (int)(($low + $high) / 2);
//            $guess = $list[$mid];
//            if ($guess == $item) {
//                return $mid;
//            }elseif ($guess > $item) {
//                $high = $mid - 1;
//            }else{
//                $low = $mid + 1;
//            }
//        }
//        return $mid;
//    }

//    public static function kcfinder($text) {
//
//        $replace = config('params.frontend').'/kcfinder/upload/images/';
//
//        $replace2 = config('params.frontend').'/kcfinder/upload/files/';
//
//        $result = str_replace('/kcfinder/upload/images/',$replace,$text);
//
//        $result = str_replace('/kcfinder/upload/files/',$replace2,$result);
//
//        return $result;
//
//        }


//    public function resizeImage(){
//
//    }

//    public static function set_active( $route ) {
//        if( is_array( $route ) ){
//            return in_array(Request::path(), $route) ? 'active' : '';
//        }
//        return Request::path() == $route ? 'active' : '';
//    }
    // bu view.php filega yoziladu <li class = "{{ set_active('admin/users') }}"><a href="{{ url('/admin/users/') }}">Users</a></li>




}
