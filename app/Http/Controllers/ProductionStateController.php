<?php

namespace App\Http\Controllers;

use stdClass;

class ProductionStateController extends Controller
{
    public function index()
    {
        $productionStates = json_decode(file_get_contents(storage_path() . "/data/ProductieStaat.json"));

        $result = [];
        foreach ($productionStates as $productionState) {
            $saw = $productionState->saw;

            foreach ($saw as $profileName => $profile) {
                //check if this sawobject has a color object
                if (!isset($saw->profielkleur->title)) {
                    continue;
                }
                $color = $saw->profielkleur->title;

                /** find all gnumber instances in the profile name
                 *  pattern is either G or g, followed by one or more digits
                 */
                preg_match_all('/[gG]\d+/', $profileName, $matches);

                // if no matches are found, continue to the next profile
                if (!count($matches[0])) {
                    continue;
                }

                foreach ($matches[0] as $gNumber) {
                    $gNumber = strtoupper($gNumber);

                    //create a new object with the count and length of the profile
                    $data = new stdClass();
                    $data->count = $profile->amount;
                    $data->length = $profile->value;

                    //check if the color and gNumber exist in the result array
                    if (isset($result[$color][$gNumber])) {
                        //check if this length already exists in the Gnumber array
                        foreach ($result[$color][$gNumber] as $key => $existingProfile) {
                            if ($existingProfile->length === $data->length) {
                                //if the length already exists, add the count to the existing count
                                $result[$color][$gNumber][$key]->count += $data->count;
                                continue 2;
                            }

                            /**
                             * if the length cannot be found in the array,
                             * that means this is a new length, so add the new object
                             */
                            if ($key === array_key_last($result[$color][$gNumber])) {
                                $result[$color][$gNumber][] = $data;
                            }
                        }
                    } else {
                        //add the new gNumber to the result array
                        $result[$color][$gNumber] = [$data];
                    }
                }
            }
        }
        dd($result);
    }
}
