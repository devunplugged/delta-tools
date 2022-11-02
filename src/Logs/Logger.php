<?php
namespace DeltaTools\Logs;

class Logger implements \Psr\Log\LoggerInterface
{
    private $logsPath = '/logs/log.txt';

    /**
     * System is unusable.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context = array()):void
    {
        $this->log(\Psr\Log\LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = array()):void
    {
        $message = $this->createMessage($level, $message, $context);

        switch($level){
            case \Psr\Log\LogLevel::EMERGENCY:
                $this->sendSMS($this->createSmsMessage($level, $message, $context));
                $this->sendEmail($this->createMailMessage($level, $message, $context));
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::ALERT:
                $this->sendSMS($this->createSmsMessage($level, $message, $context));
                $this->sendEmail($this->createMailMessage($level, $message, $context));
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::CRITICAL:
                $this->sendSMS($this->createSmsMessage($level, $message, $context));
                $this->sendEmail($this->createMailMessage($level, $message, $context));
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::ERROR:
                $this->sendEmail($this->createMailMessage($level, $message, $context));
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::WARNING:
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::NOTICE:
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::INFO:
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
            case \Psr\Log\LogLevel::DEBUG:
                $this->writeLog($this->createMessage($level, $message, $context));
                break;
        }
    }


    private function replacePlaceholders($message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    private function createSmsMessage($level, $message, array $context)
    {
        $message = $this->replacePlaceholders($message, $context);

        $log = (new \DateTime())->format('Y-m-d H:i:s');
        $log .= ' - ' . $level . ' - ';

        if(is_object($message) || is_array($message)){
            $log .= var_dump($message);
        }else{
            $log .= $message;
        }

        return $log;
    }

    private function createMessage($level, $message, array $context)
    {
        $message = $this->replacePlaceholders($message, $context);

        $log = (new \DateTime())->format('Y-m-d H:i:s');
        $log .= ' - ' . $level . ' - ';

        if(is_object($message) || is_array($message)){
            $log .= var_dump($message);
        }else{
            $log .= $message;
        }

        if(isset($context['exception']) && is_object($context['exception']) && $context['exception'] instanceof \Exception){
            $log .= ' EXCEPTION: ' . $context['exception']->getMessage();
        }

        return $log;
    }

    private function createMailMessage($level, $message, array $context)
    {
        $message = $this->replacePlaceholders($message, $context);

        $log = (new \DateTime())->format('Y-m-d H:i:s');
        $log .= ' - ' . $level;

        $log .= '<div>';
        if(is_object($message) || is_array($message)){
            $log .= var_dump($message);
        }else{
            $log .= $message;
        }
        $log .= '</div>';

        if(isset($context['exception']) && is_object($context['exception']) && $context['exception'] instanceof \Exception){
            $log .= '<div>EXCEPTION:</div><div>' . $context['exception']->getMessage() . '</div>';
        }

        return wordwrap($log, 70, "\r\n");
    }

    private function sendSMS($message)
    {
        $sms = new \DeltaTools\Messaging\SmsApi();
        $sms->setTo('607851244');
        $sms->setMessage($message);
        $sms->send();
    }

    private function writeLog($message)
    {
        file_put_contents($this->logsPath, $message.PHP_EOL , FILE_APPEND | LOCK_EX);
    }

    private function sendEmail($message)
    {
        // To send HTML mail, the Content-type header must be set
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';

        // Additional headers
        $headers[] = 'To: niewielkikazimierz@gmail.com';

        mail('niewielkikazimierz@gmail.com', 'DELTA-LOG', $message, implode("\r\n", $headers));
    }
}