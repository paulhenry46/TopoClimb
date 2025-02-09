<div>
<script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
  <script type="text/paperscript" canvas="myCanvas">
    console.log('marche');
    var path;
    var strokeWidth = 10;
    var strokeColor = 'black';
    var group;
    var num_line = '211';
    
    function onMouseDown(event) {
        // If we produced a path before, deselect it:
        if (path) {
            path.remove();
        }
        // Create a new path and set its stroke color to black:
        path = new Path({
            segments: [event.point],
            strokeColor: strokeColor,
            strokeWidth : strokeWidth,
            name : 'path_' + num_line
        });
    }
    
    // While the user drags the mouse, points are added to the path
    // at the position of the mouse:
    function onMouseDrag(event) {
        path.add(event.point);
    }
    
    // When the mouse is released, we simplify the path:
    function onMouseUp(event) {
        // When the mouse is released, simplify it:
        path.simplify(10);
        group = new Group([path]);
        group.name = 'id_' + num_line;
    }
    
    function exportToJSON(){
        console.log(group.exportJSON());
    }
</script>
  <canvas id="myCanvas" resize></canvas>
</div>