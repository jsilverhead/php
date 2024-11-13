```php
<?php  
  
class Node {  
    public string $name;  
    public array $linked = [];  
  
    public function __construct(string $name) {  
        $this->name = $name;  
    }  
  
    public function linked(Node $node) {  
        foreach($this->linked as $link) {  
            if($link->name === $node->name) { return true;}  
        }  
        return false;  
    }  
    public function linkTo(Node $node, $also = true) {  
        if (!$this->linked($node)) {$this->linked[] = $node;}  
        if ($also) {$node->linkTo($this, false);}  
        return $this;  
    }  
    public function notVisitedNodes(array $visited) {  
        $ret = [];  
        foreach($this->linked as $node) {  
            if(!in_array($node->name, $visited)) {$ret[] = $node;}  
        }  
        return $ret;  
    }  
}  
  
$root = new Node("start");  
foreach (range(1,6) as $i) {  
    $name = "node{$i}";  
    $$name = new Node($name);
}  
  
$root->linkTo($node1)->linkTo($node2);  
$node1->linkTo($node3)->linkTo($node4);  
$node2->linkTo($node5)->linkTo($node6);  
$node4->linkTo($node5);  
  
function dfs(Node $node, $path = '', $visited = array())  
{  
    $visited[] = $node->name;  
    $not_visited = $node->notVisitedNodes($visited);  
    if(empty($not_visited)) {  
        echo 'Path : ' . $path . '->' . $node->name . PHP_EOL;  
        return;  
    }  
    foreach ($not_visited as $next) {  
        dfs($next, $path . '->' . $node->name, $visited);  
    }  
}  
  
dfs($root);
```