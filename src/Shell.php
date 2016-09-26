<?php
// Класс работы с shell
// https://github.com/nramenta/clio
// https://github.com/lijinma/php-cli-color
// https://github.com/maximebf/ConsoleKit
// https://blog.shaharia.com/colorful-command-line-output-in-php/
// x https://github.com/dealnews/Console/blob/master/src/Console.php

// --------------------------------------------------------------------------------------------------------------------------
final class cli
{

    /**
     * Verbosity levels
     */
    const VERBOSITY_QUIET   = 1;
    const VERBOSITY_NORMAL  = 2;
    const VERBOSITY_VERBOSE = 3;
    const VERBOSITY_INFO    = 4;
    const VERBOSITY_DEBUG   = 16;
    /**
     * Settings for the optional argument for command line options
     */
    const OPTIONAL          = 256;
    const REQUIRED          = 512;
    const ONE_REQUIRED      = 1024;
    /**
     * Return values for the checkPid function
     */
    const PID_OK                = 8192;
    const PID_OTHER_RUNNING     = 16384;
    const PID_OTHER_NOT_RUNNING = 32767;
    const PID_OTHER_UNKNOWN     = 65534;
    /**
     * Stores the verbosity for this console application
     *
     * @var int
     */
    protected static $verbosity = self::VERBOSITY_NORMAL;
    /**
     * The unique PID file for this command
     *
     * @var string
     */
    protected $pid_file = "";
    /**
     * The last pid which ran this command
     *
     * @var integer
     */
    protected $last_pid = 0;
    /**
     * The timestamp the last time the same command was run
     *
     * @var mixed
     */
    protected $last_pid_start_time = null;
    /**
     * Holds various config options
     *
     * @var array
     */
    protected $config = array(
        "wrap" => 78,
        "copyright" => array(),
        "help" => array(
            "header" => "",
            "footer" => ""
        )
    );

    /**
     * Holds the defined options
     *
     * There are some built in options.
     *
     *  -h for help
     *  -v for verbosity
     *  -q for quiet with overrides verbosity
     *
     * @var array
     */
    protected $options = array(
        "h" => array(
            "optional"       => self::OPTIONAL,
            "description"    => "Shows this help"
        ),
        "v" => array(
            "optional"       => self::OPTIONAL,
            "description"    => "Be verbose. Additional v will increase verbosity. e.g. -vvv",
            "param_optional" => true
        ),
        "q" => array(
            "optional"       => self::OPTIONAL,
            "description"    => "Be quiet. Will override -v"
        )
    );


    /**
     * Builds the help output
     *
     * @param  array  $config  A configuration array
     * @param  array  $options Command line options array
     *
     * @return string
     */
    public function buildHelp(array $config, array $options)
    {
        $max_name_len = 0;
        $max_param_len = 0;
        $required_params = array();
        $optional_params = array();
        $optional_required_params = array();
        foreach ($options as $o=>$i) {
            // determine the maximum length of options and parameters
            $max_name_len = max($max_name_len, strlen($o));
            if (isset($i["param"])) {
                $max_param_len = max($max_param_len, strlen($i["param"]));
            }
            if ($o == "h") {
                continue;
            }
            // build an example usage string
            if (strlen($o) > 1) {
                $usage = "--$o";
            } else {
                $usage = "-$o";
            }
            if (!empty($i["param"])) {
                $usage.= " ".$i["param"];
            }
            if ($i["optional"] == self::OPTIONAL) {
                $optional_params[] = $usage;
            } elseif ($i["optional"] == self::ONE_REQUIRED) {
                $optional_required_params[] = $usage;
            } else {
                $required_params[] = $usage;
            }
        }
        $usage_example = " -h";
        if (!empty($required_params)) {
            $usage_example.= " | ".implode(" | ", $required_params);
        }
        if (!empty($optional_required_params)) {
            $usage_example.= " [".implode(" | ", $optional_required_params)."]";
        }
        if (!empty($optional_params)) {
            $usage_example.= " [".implode("] [", $optional_params)."]";
        }
        $help = "";
        if (!empty($config["help"]["header"])) {
            $help.= wordwrap($config["help"]["header"], $config["wrap"])."\n";
        }
        $help.= "USAGE:\n";
        $help.= "  ".basename($_SERVER["PHP_SELF"])." $usage_example\n\n";
        $help.= "OPTIONS:\n";
        $name_pad = $max_name_len + 2;
        $param_pad = $max_param_len + 2;
        foreach ($options as $o=>$i) {
            if (strlen($o) > 1) {
                $opt = "  --";
            } else {
                $opt = "   -";
            }
            $opt.= str_pad($o, $name_pad);
            if ($max_param_len>0) {
                if (!empty($i["param"])) {
                    $param = $i["param"];
                } else {
                    $param = "";
                }
                $opt.= str_pad($param, $param_pad);
            }
            if (!empty($i["description"])) {
                $opt.= wordwrap($i["description"], $config["wrap"] - (strlen($opt)), "\n".str_repeat(" ", strlen($opt)));
            }
            $help .= "$opt\n";
        }
        if (!empty($config["copyright"])) {
            if (empty($config["copyright"]["owner"])) {
                trigger_error("Copyright owner is required", E_USER_ERROR);
            }
            $help.= "\n";
            $help.= "Copyright {$config["copyright"]["owner"]} ";
            if (!empty($config["copyright"]["year"])) {
                $help.= " ".$config["copyright"]["year"];
            }
            $help.= "\n";
        }
        if (!empty($config["help"]["footer"])) {
            $help.= "\n";
            $help.= $config["help"]["footer"]."\n";
        }
        $help.= "\n";
        return $help;
    }
    /**
     * Shows the help and an optional message. Exits with provided exit status
     *
     * @param   type    $message    A message to show above the help
     * @param   int     $exit       Exit status code
     * @return  void
     *
     */
    public function showHelp($message = "", $exit = 0)
    {
        $bt = debug_backtrace();
        if (!empty($message)) {
            fputs(STDERR, wordwrap($message)."\n\n");
        }
        $help = $this->buildHelp($this->config, $this->options);
        echo $help;
        exit((int)$exit);
    }
    /**
     * Check for existing pid file
     *
     * This function will create a pid file for the current process.
     * If a pid file exists, it will check to see if the process is running
     * and return of the PID_* constants.
     *
     * This function will exit if an existing pid file is found.
     *
     * @param  boolean     $use_arguments  If true, command line options will be
     *                                     used to create a unique pid file name
     * @param  string      $unique_id      Optional unique identifier used to
     *                                     create the pid file name
     * @return mixed
     */
    public function checkPid($use_arguments = true, $unique_id = null)
    {
        if ($use_arguments) {
            $opts = $this->opts;
            // remove the built paramaters that control verbosity
            // They don't likely effect the job the script is doing
            if (isset($opts["v"])) {
                unset($opts["v"]);
            }
            if (isset($opts["q"])) {
                unset($opts["q"]);
            }
        } else {
            $opts = array();
        }
        $this->pid_file = $this->generatePidFilename($opts, $unique_id);
        if (!file_exists($this->pid_file)) {
            $status = self::PID_OK;
            // write the pid file
            $fp = fopen($this->pid_file, "w");
            if ($fp) {
                fputs($fp, getmypid()."|".time());
                fclose($fp);
                if (self::$verbosity == self::VERBOSITY_DEBUG) {
                    echo "Creating PID file $this->pid_file\n";
                }
            } else {
                if (self::$verbosity != self::VERBOSITY_QUIET) {
                    trigger_error("Failed to create PID file $this->pid_file", E_USER_WARNING);
                }
            }
            register_shutdown_function(array($this, "clearPid"));
        } else {
            if (self::$verbosity == self::VERBOSITY_DEBUG) {
                echo "Found existing PID file $this->pid_file\n";
            }
            list($pid, $started) = explode("|", file_get_contents($this->pid_file));
            if ($pid == getmypid()) {
                $status = self::PID_OK;
            } else {
                $this->last_pid = $pid;
                $this->last_pid_start_time = $started;
                if (file_exists("/proc")) {
                    if (file_exists("/proc/$pid/status")) {
                        if (self::$verbosity == self::VERBOSITY_DEBUG) {
                            echo "Command still running with PID $this->last_pid\n";
                        }
                        $status = self::PID_OTHER_RUNNING;
                    } else {
                        if (self::$verbosity == self::VERBOSITY_DEBUG) {
                            echo "Command no longer running with PID $this->last_pid\n";
                        }
                        $status = self::PID_OTHER_NOT_RUNNING;
                    }
                } else {
                    if (self::$verbosity == self::VERBOSITY_DEBUG) {
                        echo "Unknown status of last PID $this->last_pid\n";
                    }
                    $status = self::PID_OTHER_UNKNOWN;
                }
            }
        }
        return $status;
    }
    /**
     * Clear the pid file for the current process
     *
     * @return bool
     */
    public function clearPid($pid_file = null)
    {
        if ($pid_file === null) {
            $pid_file = $this->pid_file;
        }
        $sucess = false;
        if (empty($pid_file)) {
            if (self::$verbosity != self::VERBOSITY_QUIET) {
                trigger_error("No PID file to clear.", E_USER_WARNING);
            }
        } else {
            if (file_exists($pid_file)) {
                $success = @unlink($pid_file);
                if (!$success) {
                    if (!is_writable($pid_file)) {
                        if (self::$verbosity != self::VERBOSITY_QUIET) {
                            trigger_error("Invalid permisssion to clear PID file $pid_file.", E_USER_WARNING);
                        }
                    }
                } else {
                    if (self::$verbosity == self::VERBOSITY_DEBUG) {
                        echo "Removed PID file $pid_file\n";
                    }
                }
            } else {
                if (self::$verbosity != self::VERBOSITY_QUIET) {
                    trigger_error("PID file $pid_file not found.", E_USER_NOTICE);
                }
            }
        }
        return $success;
    }

    /**
     * Generates an array of PID filenames.
     *
     * @param  boolean     $opts        An array of options like those returned
     *                                  from getopt that can be used to generate
     *                                  a unique PID file name.
     * @param  string      $unique_id   Optional unique identifier used to
     *                                  create the pid file name
     * @return string
     */
    public function generatePidFilename(array $opts = array(), $unique_id = null)
    {
        $pid_file = sys_get_temp_dir();
        $pid_file.= DIRECTORY_SEPARATOR;
        $pid_file.= preg_replace("/[^A-Za-z0-9_-]+/", "_", basename($_SERVER["PHP_SELF"]));
        if (!empty($opts)) {
            $pid_file.= "_".sha1(serialize($opts));
        }
        if (!empty($unique_id)) {
            $pid_file.= "_".$unique_id;
        }
        $pid_file.= ".pid";
        return $pid_file;
    }


    public static function shell()
    {

    }
}
$n=new cli();
echo $n->generatePidFilename();
