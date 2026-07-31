const fs = require('fs');
const file = 'resources/js/Components/widgets/lock.json';
const lock = JSON.parse(fs.readFileSync(file));

// Scale factor (percentage)
const scaleFactor = 200;

// Remove previous Null layer if it exists to allow re-scaling multiple times
const previousNullIndex = lock.layers.findIndex(l => l.nm === "Scale_Null");
let existingNullInd = null;
if (previousNullIndex !== -1) {
    existingNullInd = lock.layers[previousNullIndex].ind;
    lock.layers.splice(previousNullIndex, 1);
    // Remove parent ref from layers that had it
    lock.layers.forEach(l => {
        if (l.parent === existingNullInd) {
            delete l.parent;
        }
    });
}

let maxInd = 0;
lock.layers.forEach(l => {
    if (l.ind && l.ind > maxInd) maxInd = l.ind;
});
const newInd = maxInd + 1;

const nullLayer = {
    ty: 3,
    nm: "Scale_Null",
    ind: newInd,
    ip: 0,
    op: lock.op || 99999,
    st: 0,
    bm: 0,
    sr: 1,
    ks: {
        o: { a: 0, k: 100, ix: 11 },
        r: { a: 0, k: 0, ix: 10 },
        p: { a: 0, k: [lock.w / 2, lock.h / 2, 0], ix: 2 },
        a: { a: 0, k: [lock.w / 2, lock.h / 2, 0], ix: 1 },
        s: { a: 0, k: [scaleFactor, scaleFactor, 100], ix: 6 }
    }
};

lock.layers.forEach(l => {
    if (l.parent === undefined) {
        l.parent = newInd;
    }
});

lock.layers.push(nullLayer);

fs.writeFileSync(file, JSON.stringify(lock));
console.log('Successfully scaled Lottie to ' + scaleFactor + '%');
