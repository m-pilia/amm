<?php
/**
 * \brief Abstract class used as template for enumeration classes.
 *
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-04-29
 */
abstract class Enum {

    /**
     * \brief Check wether a variable is a valid enumeration value.
     * @param  $value Variable to be checked.
     * @return boolean True if the parameter is a valid value for the current
     * enumeration, false otherwise.
     */
    public static function isValid($value) {
        $reflector = new ReflectionClass(get_called_class());
        return in_array(
            $value,
            array_values($reflector->getConstants()),
            $strict = true);
    }
}
?>
