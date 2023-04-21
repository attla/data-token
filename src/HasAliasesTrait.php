<?php

namespace Attla\DataToken;

use Illuminate\Support\Arr;

trait HasAliasesTrait
{
    /**
     * The token instance
     *
     * @var array<string, string[]>
     */
    protected $methodAliases = [];

    protected function registerAliases()
    {
        $aliases = [];
        foreach ($this->aliases as $method => $aliasList) {
            array_walk(
                Arr::where(
                    Arr::flatten((array) $aliasList),
                    fn ($value) => is_string($value)
                ),
                fn($alias) => $aliases[$alias] = $method
            );
        }

        $this->aliases = $aliases;
    }

    /**
     * Dynamically call method aliases
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|$this
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if (!is_null($alias = $this->aliases[$name] ?? null)) {
            $name = $alias;
        }

        foreach ($this->aliasOrigin as $origin) {
            if (property_exists($origin, $name)) {
                return $origin->{$name};
            } elseif (method_exists($origin, $name)) {
                $result = $origin->{$name}(...$arguments);

                return $this;
            }
        }

        if ($this->token->isset($name) || $this->token->hasMethod($name)) {
            $this->token->{$name}(...$arguments);

            return $this;
        }

        throw new \BadMethodCallException('Method "' . $name . '" not exists on ' . __NAMESPACE__);
    }
}
