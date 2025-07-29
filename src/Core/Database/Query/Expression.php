<?
declare(strict_types=1);
php\np



namespace IslamWiki\Core\Database\Query;

class Expression
{
    /**
     * The value of the expression.
     */
    protected string $value;

    /**
     * Create a new raw query expression.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get the value of the expression.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the value of the expression.
     */
    public function __toString(): string
    {
        return $this->getValue();
    }
}
