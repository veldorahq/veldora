<?php
// Veldora UI — DataTable Component
// Props: columns (assoc array key=>label), rows (array of assoc arrays), searchable (bool), perPage (int)
$columns    = $columns    ?? [];
$rows       = $rows       ?? [];
$searchable = !empty($searchable);
$perPage    = (int)($perPage ?? 10);
$uid = 'dt-' . substr(md5(uniqid()), 0, 6);
$colKeys = array_keys($columns);
?>
<div class="vui-datatable-wrap" id="<?= $uid ?>">
    <?php if ($searchable): ?>
        <div class="vui-datatable-toolbar">
            <input type="search" class="vui-input vui-datatable-search" placeholder="Search..."
                   oninput="vuiDt_<?= $uid ?>_search(this.value)">
        </div>
    <?php endif; ?>
    <div class="vui-table-responsive">
        <table class="vui-table vui-table-hover vui-table-striped">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $lbl): ?>
                        <th onclick="vuiDt_<?= $uid ?>_sort('<?= htmlspecialchars((string)$key) ?>')" style="cursor:pointer">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span><?= htmlspecialchars((string)$lbl) ?></span>
                                <svg class="vui-sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody id="<?= $uid ?>-tbody">
                <?php foreach ($rows as $row): ?>
                    <tr><?php foreach ($colKeys as $k): ?>
                        <td><?= htmlspecialchars((string)($row[$k] ?? '')) ?></td>
                    <?php endforeach; ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="<?= $uid ?>-pages" class="vui-datatable-pages"></div>
</div>
<script>
(function(){
    var uid='<?= $uid ?>',perPage=<?= $perPage ?>,page=1,q='',asc=true,sKey='';
    var tbody=document.getElementById(uid+'-tbody');
    var allRows=Array.from(tbody.querySelectorAll('tr'));
    function filtered(){return q?allRows.filter(function(r){return r.textContent.toLowerCase().includes(q);}):allRows.slice();}
    function render(){
        var f=filtered();
        var tot=Math.ceil(f.length/perPage)||1;
        if(page>tot)page=1;
        tbody.innerHTML='';
        f.slice((page-1)*perPage,page*perPage).forEach(function(r){tbody.appendChild(r);});
        var pg=document.getElementById(uid+'-pages');
        pg.innerHTML='';
        for(var i=1;i<=tot;i++){
            var b=document.createElement('button');
            b.textContent=i;b.className='vui-page-btn'+(i===page?' vui-page-active':'');
            b.setAttribute('data-p',i);
            b.onclick=(function(p){return function(){page=p;render();};})(i);
            pg.appendChild(b);
        }
    }
    window['vuiDt_'+uid+'_search']=function(v){q=v.toLowerCase();page=1;render();};
    window['vuiDt_'+uid+'_sort']=function(k){
        if(sKey===k)asc=!asc;else{sKey=k;asc=true;}
        var idx=<?= json_encode($colKeys) ?>.indexOf(k);
        allRows.sort(function(a,b){
            var av=(a.cells[idx]||{}).textContent||'';
            var bv=(b.cells[idx]||{}).textContent||'';
            return asc?av.localeCompare(bv,undefined,{numeric:true}):bv.localeCompare(av,undefined,{numeric:true});
        });
        render();
    };
    render();
}());
</script>