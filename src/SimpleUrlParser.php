<?php
/**
 * Simple URL parser. Parse url as follows: /[module]/[switch]/[param1]:[value1]/[param2]:[/value2]...
 */

namespace Fallenangelbg\SimpleUrlParser;

class SimpleUrlParser
{
    /**
     * @var array[]
     *
     * Names of the modules to be displayed in the Breadcrumb array
     */
    private $modulesNames;
    /**
     * @var array[][]
     * Names of the method, grouped by modules, to be used as a second step of the Breadcrumb
     */
    private $methodsNames;

    /**
     * Basic construct
     */
    public function __construct()
    {
        $this->modulesNames = array(
          "module1" => "Name 1",
          "module2" => "Name 2",
        );
        $this->methodsNames = array(
          "module2" => array(
            "method1" => "Method 1",
            "method2" => "Method 2",
          ),
        );
    }

    /**
     * @param false $skipDigitConvert If set to true, any digit-like value will be forced convert to number
     *
     * @return array Array with results from the URL
     */
    function parseUrlForResults(bool $skipDigitConvert = false): array
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
            $currentName = $this->modulesNames[$getUrlDetails[0]] ?? "";
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

            $currentMethodName = $this->methodsNames[$getUrlDetails[0]][$getUrlDetails[1]] ?? "";
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

    /**
     * Build URL from an array
     *
     * @param array $params Array with params, from which to build the URL. Format: $array['module'], $array['switch'], $array['params']['param1]=$value1;
     *
     * @return string The url, which can be parsed by @internal parseUrlForResults
     */
    function buildUrlByParams(array $params): string
    {
        $newUrl = '';
        if (!empty($params['module'])) {
            $newUrl = "/" . $params['module'];
        }
        if (!empty($params['switch'])) {
            $newUrl .= "/" . $params['switch'];
        }

        foreach ($params['params'] as $paramName => $paramValue) {
            $newUrl .= "/" . $paramName . ":" . $paramValue;
        }

        return $newUrl;
    }

    /**
     * Build an URL by post data. The $_POST data must contain only 'params' section. This method can build url base on <input> entries
     * @param string $baseUrl
     * @param array $postDataForm
     *
     * @return string
     */
    public function buildUrlByPOST(string $baseUrl = '/', array $postDataForm = array()): string
    {
        $returnUrl = $baseUrl;

        $skipKeys = array(
          "switch" => 1,
        );

        foreach ($postDataForm as $postKey => $postData) {
            if (!empty($skipKeys[$postKey]) || empty($postData)) {
                continue;
            }

            if (is_array($postData)) {
                $postData = array_filter($postData);
                if (!empty($postData)) {
                    $postData = implode(",", $postData);
                    $returnUrl .= "/" . $postKey . ":" . $postData;
                }
            } else {
                $returnUrl .= "/" . $postKey . ":" . $postData;
            }
        }

        return $returnUrl;
    }
}
