<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class FileController extends Controller
{
    public function store(Request $request) {
        $input = $request->all();

        $rules = [
            'file' => 'required|mimes:csv'
        ];

        $messages = [
            'file.mimes' => 'We can only support CSV at the moment.',
            'file.required' => 'File is required.'
        ];

        $validator = Validator::make($input, $rules, $messages);

        if($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors('file')->first());
        }


        if($request->hasFile('file')) {
            $person = [
                'title' => null,
                'first_name' => null,
                'initial' => null,
                'last_name' => null
            ];
            $singlePerson = true;

            $result = [];
            $fileName = $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
            $fileContent = $request->file->get();

            //split into lines and remove header
            $fileContent = explode("\n", $fileContent);
            array_shift($fileContent);
            $allMatches = [];

            $fields = [
                'title',
                'first_name',
                'initial',
                'last_name'
            ];

            foreach($fileContent as $row) {
                $words = [];
                $owner = [];
                $owner2 = [];
                $words = explode(' ', $row);
                //remove ',' and new line
                $words[count($words) - 1] = str_replace(",", "", $words[count($words) - 1]);
                $words[count($words) - 1] = str_replace("\r", "", $words[count($words) - 1]);

                //title
                $first = isset($words[0]) ? $words[0] : null;
                if($first == null) { continue; }
                if(empty($first)) { continue; }
                $owner['title'] = $first;

                //and / initial / first name ?
                $second = isset($words[1]) ? $words[1] : null;
                if($second != null) {
                    if($this->isInitial($second)) {
                        $owner['initial'] = $second;
                    } else if($this->isAnd($second)) {
                        $singlePerson = false;
                    } else if(count($words) <= 4) {
                        $owner['first_name'] = $second;
                    }
                }

                //last name or we keep going
                $third = isset($words[2]) ? $words[2] : null;
                if(count($words) === 3) {
                    $owner['last_name'] = $third;
                }
                
                //with 4 words it should be 2 owners with just lastname
                $fourth = isset($words[3]) ? $words[3] : null;

                if( count($words) === 4) {
                    if($fourth != null && !$singlePerson ) {
                        $owner['last_name'] = $fourth;
                        $owner2['title'] = $third;
                        $owner2['last_name'] = $fourth;
                    }
                    //&/and as a fourth word
                    if(count($words) === 4 && $this->isAnd($fourth)) {
                        $owner['last_name'] = $third;
                        $singlePerson = false;
                    }
                }
                
                //quite safe to assume we're dealing with more than one person
                if(count($words) > 4) { $singlePerson = false; }

                //there is more cases for 5 words that could happen..
                $fifth = isset($words[4]) ? $words[4] : null;
                if( count($words) === 5 && $this->isAnd($second) ) {
                    $owner['last_name'] = $fifth;
                    $owner2['title'] = $third;
                    if($this->isInitial($fourth)) {
                        $owner2['initial'] = $fourth;
                    } else {
                        $owner2['first_name'] = $fourth;
                    }
                    $owner2['last_name'] = $fifth;
                }

                $sixth = isset($words[5]) ? $words[5] : null;
                if( count($words) === 6) {
                    //check how much do we know about each owner
                    if($this->isAnd($third)) {
                        $owner['last_name'] = $second;

                        $owner2['title'] = $fourth;
                        if($this->isInitial($fifth)) {
                            $owner2['initial'] = $fifth;
                        } else {
                            $owner2['first_name'] = $fifth;
                        }
                        $owner2['last_name'] = $sixth;
                    } else if($this->isAnd($fourth)) {
                        if($this->isInitial($second)) {
                            $owner['initial'] = $second;
                        } else {
                            $owner['first_name'] = $second;
                        }
                        $owner['last_name'] = $third;

                        $owner2['title'] = $fifth;
                        $owner2['last_name'] = $sixth;
                    }
                }

                $seventh = isset($words[6]) ? $words[6] : null;
                if( count($words) === 7) {
                    if($this->isInitial($second)) {
                        $owner['initial'] = $second;
                    } else {
                        $owner['first_name'] = $second;
                    }
                    $owner['last_name'] = $third;
                    
                    $owner2['title'] = $fifth;
                    if($this->isInitial($sixth)) {
                        $owner2['initial'] = $sixth;
                    } else {
                        $owner2['first_name'] = $sixth;
                    }
                    $owner2['last_name'] = $seventh;
                }


                // fill in the rest of the fields with nulls
                foreach($fields as $field) {
                    if(!isset($owner[$field])) {
                        $owner[$field] = null;
                    }

                    if(isset($owner2) && !empty($owner2) && !isset($owner2[$field])) {
                        $owner2[$field] = null;
                    }
                }

                $allMatches[] = $owner;

                if(isset($owner2) && !empty($owner2)) {
                    $allMatches[] = $owner2;
                }
            }

            echo "<pre>";
            print_r($allMatches);
            echo "</pre>";

 
            

            // return back()->with('success','File has been uploaded.')->with('result', $result);
        }
    }

    private function isInitial($input) {
        if( (strlen($input) == 1 && $input != '&') ||
            (strlen($input) == 2 && $input[1] == '.')
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function isAnd($input) {
        if($input == '&' || $input == 'and') {
            return true;
        } else {
            return false;
        }
    }
}
