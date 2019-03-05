<?php

function filter_hosts($keyword)
{
    $content = file_get_contents(getenv("HOME") . '/.shuttle.json');
    $configs = json_decode($content, JSON_OBJECT_AS_ARRAY);
    $hosts = [];

    foreach ($configs['hosts'] as $host) {
        if (isset($host['name'])) {
            $hosts[$host['name']] = $host['cmd'];
            continue;
        }

        foreach ($host as $subName => $subHosts) {
            foreach ($subHosts as $subHost) {
                $hosts["{$subName}/{$subHost['name']}"] = $subHost['cmd'];
            }
        }
    }

    $keyword = strtolower(trim($keyword));
    if (!$keyword) {
        $result = $hosts;
    } else {
        $equals = [];
        $contains = [];
        $matched = [];
        foreach ($hosts as $name => $cmd) {
            $nameLower = strtolower($name);
            if ($nameLower === $keyword) {
                $equals[$name] = $cmd;
                continue;
            }

            if (strpos($nameLower, $keyword) !== false) {
                $contains[$name] = $cmd;
                continue;
            }

            $pattern = join('*', str_split($keyword));
            if (fnmatch("*{$pattern}*", $nameLower)) {
                $matched[$name] = $cmd;
                continue;
            }
        }
        $result = array_merge($equals, $contains, $matched);
    }

    $items = [];
    foreach ($result as $name => $cmd) {
        $items[] = [
            'title' => $name,
            'subtitle' => $cmd,
            'arg' => $cmd,
        ];
    }
    $data = ['items' => $items];

    return json_encode($data);
}