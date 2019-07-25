<?php

namespace Shy\Http\Contracts;

interface View
{
    /**
     * Set view file
     *
     * @param string $view
     * @return view
     */
    public function view(string $view);

    /**
     * Set layout file
     *
     * @param $layout
     * @return $this
     */
    public function layout($layout);

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
