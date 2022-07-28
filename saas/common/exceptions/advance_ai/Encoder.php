<?php
namespace common\exceptions\advance_ai;

class EncoderException extends \RuntimeException
{
}

class UnsupportedCharsetException extends EncoderException
{
}

class EmptyArgumentException extends EncoderException
{
}

class NullArgumentException extends EncoderException
{
}

class MobileFormatException extends EncoderException
{
}

class NameFormatException extends EncoderException
{
}

class NIKFormatException extends EncoderException
{
}

abstract class Encoder
{
    protected $value = "";

    public function __construct($v)
    {
        $this->value = $v;

        if ($this->value === null) {
            throw new NullArgumentException("The argument is null");
        }

        if ($this->value === '') {
            throw new EmptyArgumentException("The argument is empty");
        }

        if (!mb_check_encoding($this->value, 'UTF-8')) {
            throw new UnsupportedCharsetException("Only Support UTF-8");
        }
    }

    abstract public function format();

    public function encodeBySha1WithSalt($salt)
    {
        return sha1($salt . $this->format());
    }
}

class MobileEncoder extends Encoder
{
    const MOBILE_REGEX = '/^\\+62\\d{3,18}$/';

    public function format()
    {
        if (!preg_match(self::MOBILE_REGEX, $this->value)) {
            throw new MobileFormatException("The mobile format is invalid: $this->value");
        }
        return $this->value;
    }
}

class NameEncoder extends Encoder
{
    const NAME_REGEX = '/^[A-Za-z][-_A-Za-z ]*[A-Za-z]$/';

    public function format()
    {
        if (!preg_match(self::NAME_REGEX, $this->value)) {
            throw new NameFormatException("The name format is invalid: $this->value");
        }
        return $this->value;
    }
}

class NIKEncoder extends Encoder
{
    const NIK_REGEX = '/^[0-9]{16}$/';

    public function format()
    {
        if (!$this->isValidNIKFormat()) {
            throw new NIKFormatException("The NIK format is invalid: $this->value");
        }
        return $this->value;
    }

    private function isValidNIKFormat()
    {
        return $this->isValidNumber() && $this->isValidDate() && $this->isValidBirthMonth() && $this->isValidAgeRange();
    }

    private function isValidNumber()
    {
        return preg_match(self::NIK_REGEX, $this->value);
    }

    private function isValidDate()
    {
        $highDate = substr($this->value, 6, 1);
        $lowDate = substr($this->value, 7, 1);
        $result = true;
        if ($highDate == 3 || $highDate == 7) {
            $result = $lowDate < 2;
        } elseif ($highDate == 0 || $highDate == 4) {
            $result = $lowDate > 0;
        }
        return $highDate < 8 && $result;
    }

    private function isValidBirthMonth()
    {
        $month = substr($this->value, 8, 2);
        return $month > 0 && $month < 13;
    }

    private function isValidAgeRange()
    {
        $minValidAge = 16;
        $maxValidAge = 80;
        $age = $this->calculateAge();
        return $age >= $minValidAge && $age <= $maxValidAge;
    }

    private function calculateAge()
    {
        //TODO: we do not care timezone now.
        $nowYear = date('Y');
        $tailOfNowYear = $nowYear % 2000;
        $yearOfNik = substr($this->value, 10, 2);
        $yearOfBirth = 1900;
        if ($yearOfNik > $tailOfNowYear) {
            $yearOfBirth += $yearOfNik;
        } else {
            $yearOfBirth += $yearOfNik + 100;
        }
        return $nowYear - $yearOfBirth;
    }

}
