
function clone(obj) {
  return JSON.parse(JSON.stringify(obj));
}

// 折叠数据
function toTree(nodes) {
  nodes = nodes || [];
  if (nodes.length <= 1) {
    return nodes;
  }

  const nodesById = {
    0: {},
  };

  nodes.forEach(node => {
    nodesById[node.id] = node;
  });

  nodes.forEach(node => {
    if (node.prev_id) {
      nodesById[node.prev_id].next_id = node.id;
    } else {
      const parent_id = node.parent_id || 0;
      nodesById[parent_id].child_id = node.id;
    }
  });

  return getChildNodes(nodesById, 0);
}

function getChildNodes(nodes, parent_id) {
  const children = [];
  const parent = nodes[parent_id];
  let node = nodes[parent.child_id];
  while (node) {
    children.push(node);
    node.children = getChildNodes(nodes, node.id);
    node = nodes[node.next_id];
  }
  return children;
}

// 拆分数据
function toRecords(treeData, parent, prev, path) {
  let records = [];

  path = path || '/';
  treeData.forEach(node => {
    records.push({
      id: node.id,
      parent_id: parent || null,
      prev_id: prev || null,
      path: path || '/',
    });
    if (node.children && node.children.length) {
      records = records.concat(toRecords(node.children, node.id, null, path + node.id + '/'));
    }
    prev = node.id;
  });

  return records
}

function isEmptyObject(obj) {
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      return false;
    }
  }
  return true;
}

function stringify(tar) {
  return JSON.stringify(tar, (prop, val) => {
    const _type = typeof val;
    if(_type == 'string') {
      val = val.trim();
      if(!isNaN(val*1)) val = val*1;
    } else if (_type == 'boolean') {
      val = val*1;
    }
    return val;
  })
}

function isEqual(v1, v2) {
  const a1 = _.isArray(v1);
  const a2 = _.isArray(v2);
  if (a1 || a2) {
    if (a1 && a2) {
      if (v1.length !== v2.length) {
        return false;
      }
      return _.difference(v1, v2).length === 0;
    }
    return false;
  }
  // return v1 == v2 || stringify(v1) == stringify(v2);
  return _.isEqual(v1, v2);
}
