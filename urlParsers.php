<?php
/**
 * Simple URL parser. Parse url as follows: /[module]/[switch]/[param1]:[value1]/[param2]:[/value2]...
 *
 * @param boolean Skip check for digits in parameter
 *
 * @return array Array with data, fetched from URL
 */
function parseUrlForResults($skipDigitConvert = false): array
{
    $requestPath = '';
    if ($_SERVER["REQUEST_URI"]) {
        $requestPath = $_SERVER["REQUEST_URI"];
        $requestPath = preg_replace("/^\//", '', $requestPath);
        $requestPath = preg_replace("/\/$/", '', $requestPath);
    }

    $returnArray['url'] = $requestPath;
    if (preg_match("/.*?\/p(\d*?)$/", $requestPath, $matches)) {
        $returnArray['page'] = $matches[1];
    }
    $returnArray['bread']['Начало'] = "/";

    $getUrlDetails = explode("/", $requestPath);

    if (!empty($getUrlDetails[0])) {
        $returnArray['module'] = $getUrlDetails[0];
        $currentName = $modulesNames[$getUrlDetails[0]] ?? "";
        if (!empty($currentName)) {
            $returnArray['bread'][$currentName] = $getUrlDetails[0] . "/";
            $returnArray['page_title'] = " " . $currentName;
        } else {
            $returnArray['bread'][$getUrlDetails[0]] = $getUrlDetails[0] . "/";
        }
    } else {
        $returnArray['module'] = "index";
    }

    if (!empty($getUrlDetails[1])) {
        $returnArray['switch'] = $getUrlDetails[1];

        $currentMethodName = $methodsNames[$getUrlDetails[0]][$getUrlDetails[1]] ?? "";
        if (!empty($currentMethodName)) {
            if (!empty($getUrlDetails[2])) {
                $returnArray['bread'][$currentMethodName] = $getUrlDetails[1] . "/" . $getUrlDetails[2] . "/";
            } else {
                $returnArray['bread'][$currentMethodName] = $getUrlDetails[1] . "/";
            }
            $returnArray['page_title'] = " " . $currentMethodName;
        } else {
            if (!empty($getUrlDetails[2])) {
                $returnArray['bread'][$getUrlDetails[1]] = $getUrlDetails[1] . "/" . $getUrlDetails[2] . "/";
            } else {
                $returnArray['bread'][$getUrlDetails[1]] = $getUrlDetails[1] . "/";
            }
        }
    }

    for ($i = 2; $i < count($getUrlDetails); $i++) {
        $getPairs = explode(":", $getUrlDetails[$i]);
        if (!isset($getPairs[1])) {
            continue;
        }
        if (stripos($getPairs[1], ",") !== false) {
            $returnValues = explode(",", $getPairs[1]);
            $explodedValue = array();

            foreach ($returnValues as $valueData) {
                if ($skipDigitConvert === true) {
                    $explodedValue[] = htmlspecialchars($valueData, ENT_QUOTES);
                } else {
                    if (ctype_digit($valueData)) {
                        $explodedValue[] = intval($valueData);
                    } else {
                        $explodedValue[] = htmlspecialchars($valueData, ENT_QUOTES);
                    }
                }
            }
            $returnArray['params'][$getPairs[0]] = $explodedValue;
        } else {
            if ($skipDigitConvert === true) {
                $value = htmlspecialchars($getPairs[1], ENT_QUOTES);
            } else {
                if (ctype_digit($getPairs[1])) {
                    $value = intval($getPairs[1]);
                } else {
                    $value = htmlspecialchars($getPairs[1], ENT_QUOTES);
                }
            }
            $returnArray['params'][$getPairs[0]] = $value;
        }
    }

    return $returnArray;
}
