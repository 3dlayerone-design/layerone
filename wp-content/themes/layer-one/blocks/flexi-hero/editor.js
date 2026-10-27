/* Collectibles hero: editor UI. */
(function (wp) {
  var L = window.LOEd, el = L.el, c = wp.components;
  var useBlockProps = wp.blockEditor.useBlockProps;

  function Edit(props) {
    var a = props.attributes;
    function setA(k, v) { var o = {}; o[k] = v; props.setAttributes(o); }
    function t(label, key, extra) { return L.Text(label, a[key], function (v) { setA(key, v); }, extra); }

    return el('div', useBlockProps({ style: L.BOX }),
      L.Header('cover-image', 'Collectibles: hero', 'Heading, buttons and the wall of moving figures. {figures}, {bundles}, {price} and {n} are filled in from your products.'),
      el(c.PanelBody, { title: 'Texts', initialOpen: true },
        L.Row([t('Small badge', 'badge'), t('Line next to the badge', 'eyebrow')]),
        L.Row([t('Heading', 'heading'), t('Heading, italic part', 'headingAccent')]),
        L.Area('Intro text', a.intro, function (v) { setA('intro', v); }),
        t('"From" line', 'fromText', { help: '{price} = cheapest price per figure, {n} = biggest box. <b> makes text bold.' }),
        L.Row([t('Button 1 text', 'btn1'), t('Button 2 text', 'btn2')])
      ),
      el(c.PanelBody, { title: 'Figure wall', initialOpen: false },
        el('p', { style: { color: '#50575e' } }, 'Choose a product tag, or pick figures by hand. Picked figures win. With neither, figures that have a video are shown.'),
        el(L.TagAndProducts, { tag: a.wallTag, ids: a.wallProducts, onTag: function (v) { setA('wallTag', v); }, onIds: function (v) { setA('wallProducts', v); } })
      )
    );
  }

  wp.blocks.registerBlockType('layerone/flexi-hero', { edit: Edit, save: function () { return null; } });
})(window.wp);
