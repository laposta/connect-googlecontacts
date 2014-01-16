<?php

namespace {

    interface Printable
    {
        /**
         * @return string
         */
        public function toString();

        /**
         * @return string
         */
        public function __toString();
    }
}
