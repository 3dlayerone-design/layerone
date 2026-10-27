/* Flexi Crew band: editor UI. Figures and bundles come from the products; here you choose which ones. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    var opts = L.useFlexiOptions();
    var tags = opts ? opts.tags : [];
    var tagOptions = [{ label: 'Choose a tag…', value: '' }].concat(tags.map(function (t) { return { label: t.name + ' (' + t.count + ')', value: t.slug }; }));
    if (a.wallTag && !tags.some(function (t) { return t.slug === a.wallTag; })) tagOptions.push({ label: a.wallTag, value: a.wallTag });

    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('images-alt2', 'Flexi Crew band', 'Dark green band with the figure wall and bundle slider. {figures}, {series}, {bundles}, {price} and {n} are filled in from your products.'),

      el(c.PanelBody, { title: 'Heading, text and buttons', initialOpen: true },
        L.Row([L.Text('Small heading', a.kicker, function (v) { setA('kicker', v); }), L.Text('Heading', a.heading, function (v) { setA('heading', v); })]),
        L.Row([L.Text('Heading, italic part', a.headingAccent, function (v) { setA('headingAccent', v); }), L.Text('Text', a.text, function (v) { setA('text', v); })]),
        el('strong', null, 'Stats'),
        el(L.Rows, { items: a.stats, onChange: function (v) { setA('stats', v); }, cols: '1fr 2fr auto', blank: { value: '', label: '' }, addLabel: '+ Add stat',
          render: function (s, set) { return [L.Text('Number', s.value, function (v) { set('value', v); }), L.Text('Label', s.label, function (v) { set('label', v); })]; } }),
        L.Row([L.Text('Button 1 text', a.btn1Label, function (v) { setA('btn1Label', v); }), L.Text('Button 1 link', a.btn1Url, function (v) { setA('btn1Url', v); }, { type: 'url', help: 'Empty = the Collectibles page.' })]),
        L.Row([L.Text('Button 2 text', a.btn2Label, function (v) { setA('btn2Label', v); }), L.Text('Button 2 link', a.btn2Url, function (v) { setA('btn2Url', v); }, { type: 'url', help: 'Empty = the box builder.' })])
      ),

      el(c.PanelBody, { title: 'Figure wall', initialOpen: false },
        el('p', { style: { color: '#50575e' } }, 'The two moving rows. Photo, name, colour and video come from each figure product. Choose a product tag, or pick figures by hand. Picked figures win over the tag.'),
        el(c.SelectControl, { label: 'Show figures with this product tag', value: a.wallTag || '', options: tagOptions, onChange: function (v) { setA('wallTag', v); } }),
        opts ? el(L.ProductPicker, { label: 'Or pick figures by hand', ids: a.wallProducts, options: opts.figures, onChange: function (v) { setA('wallProducts', v); } }) : el(c.Spinner),
        el(c.RangeControl, { label: 'Most figures to show (0 = all)', min: 0, max: 84, value: a.wallLimit || 0, onChange: function (v) { setA('wallLimit', v || 0); } })
      ),

      el(c.PanelBody, { title: 'Bundles', initialOpen: false },
        L.Row([L.Text('Section title', a.bundlesTitle, function (v) { setA('bundlesTitle', v); }), L.Text('Text next to it', a.bundlesSub, function (v) { setA('bundlesSub', v); })]),
        el('p', { style: { color: '#50575e' } }, 'Banner, name, tagline and badge come from each bundle product. Leave the list empty to show every bundle.'),
        opts ? el(L.ProductPicker, { label: 'Choose bundles', ids: a.bundleProducts, options: opts.bundles, onChange: function (v) { setA('bundleProducts', v); } }) : el(c.Spinner)
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi', { edit: Edit, save: function () { return null; } });
})(window.wp);
