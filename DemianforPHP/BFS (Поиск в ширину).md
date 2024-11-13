```php
<?php  
  
$graph = [  
    'A' => ['B', 'C'],  
    'B' => ['A', 'D'],  
    'D' => ['B'],  
    'C' => ['A',]  
];  
  
function bfs($graph, $start, $finish)  
{  
    $queue = new SplQueue();  
    $queue->enqueue($start);  
    $visited = [$graph['you']];  
  
    while($queue->count() > 0) {  
        $node = $queue->dequeue();  
        if ($node === $finish) {  
            echo 'I found seller';  
            return true;  
        }  
  
        foreach($graph[$node] as $neighbor) {  
            if(!in_array($neighbor,$visited, true)) {  
                $visited[] = $neighbor;  
                $queue->enqueue($neighbor);  
            }  
        }  
    }  
    echo 'Nothing found';  
    return false;  
}  
  
bfs($graph, 'A', 'D');
```