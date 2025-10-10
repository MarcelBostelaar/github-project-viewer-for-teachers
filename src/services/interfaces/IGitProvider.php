<?php

interface IGitProvider {
    public function clone(string $url): string;
    public function clean();
}