<?php

namespace Shy\Http\Contracts;

interface View
{
    /**
     * Initialize in cycle
     */
    public function initialize();

    /**
     * Set view file
     *
     * @param string $view
     * @return $this
     */
    public function view(string $view);

    /**
     * Set layout file
     *
     * @param string $layout
     * @return $this
     */
    public function layout(string $layout);

    /**
     * Params pass by controller
     *
     * @param array $params
     * @return $this
     */
    public function with(array $params);

    /**
     * Render view
     *
     * @return string
     */
    public function render();

}
