/* ==========================================================================
   Nano Kids Wholesale — catalogue data (static prototype / DB-shaped)
   Fields map cleanly to a future PHP MVC / MySQL schema:
     products(id, sku, name, slug, category, age_group, season, fabric, gsm,
              moq, base_price, lead_time_days, stock_status, pack_units,
              pack_ratio, gender, description, tag)
     product_tiers(product_id, min_qty, max_qty, discount_pct)
     product_sizes / product_colors / product_images (related rows)
   MOQ is set per product (admin). Tier breaks start at that product's MOQ.
   ========================================================================== */

function pex(id, w = 1200) {
  return `https://images.pexels.com/photos/${id}/pexels-photo-${id}.jpeg?auto=compress&cs=tinysrgb&w=${w}`;
}
function img(id, w = 800, h = 1000, o = {}) {
  let u = `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&q=75&w=${w}&h=${h}`;
  if (o.fz) u += `&crop=focalpoint&fp-x=${o.fx ?? .5}&fp-y=${o.fy ?? .5}&fp-z=${o.fz}`;
  else if (o.crop) u += `&crop=${o.crop}`;
  return u;
}

const PH = {
  red:'1471286174890-9c112ffca5b4', rain:'1503919545889-aef636e10ad4', fence:'1503919005314-30d93d07d823',
  floral:'1518831959646-742c3a14ebf7', navy:'1519238263530-99bdd11df2ea', wall:'1519457431-44ccd64a579b',
  bear:'1522771930-78848d9293e8', skate:'1596870230751-ebdfce98ec42', run:'1571210862729-78a52d3779a2',
  denim:'1514090458221-65bb69cf63e6', clip:'1503454537195-1dcabb73ffb9', future:'1536640712-4d4c36ff0e4e',
  vest:'1485546246426-74dc88dec4d9', sprink:'1489710437720-ebb67ec84dd2', read:'1472162072942-cd5147eb3902',
  dresses:'1560506840-ec148e82a604', towel:'1566004100631-35d015d6a491', sleep:'1544126592-807ade215a0b',
  shoe:'1470116892389-0de5d9770b2c', float:'1519689680058-324335c77eba', cam:'1516627145497-ae6968895b74',
  whisper:'1621452773781-0f992fd1f5cb', flattee:'1622290291468-a28f7a7dc6a8', body:'1622290319146-7b63df48a635',
  sweat:'1620799140408-edc6dcb6d633', dshirt:'1596755094514-f87e34085b2c', rack:'1604467794349-0b74285de7e7',
  hang:'1601924994987-69e26d50dc26', feet:'1555252333-9f8e92e65df9', two:'1503944583220-79d8926ad5e2'
};

const CAMPAIGN = {
  knit: 5560031, everyday: 1620760, festive: 17921044, baby: 16567792,
  jumps: 6725093, trio: 14571367, formal: 29668580, hoodie: 28506036,
  jeans: 31703288, pyjama: 18820092, cube: 15179150, taupe: 30147132
};

const AGES = [
  { key:'newborn', label:'Newborn', range:'0 – 12 months', sizes:['0-3m','3-6m','6-12m'] },
  { key:'baby',    label:'Baby',    range:'1 – 3 years',   sizes:['12-18m','18-24m','2-3y'] },
  { key:'kids',    label:'Kids',    range:'4 – 8 years',   sizes:['4-5y','6-7y','7-8y'] },
  { key:'junior',  label:'Junior',  range:'9 – 14 years',  sizes:['9-10y','11-12y','13-14y'] }
];
const SEASONS = [
  { key:'summer',  label:'Summer',         note:'Jerseys, linens and lawn for warm floors and long days.' },
  { key:'winter',  label:'Winter',         note:'Fleece, knits and quilted layers, packed for the season.' },
  { key:'spring',  label:'Spring / Autumn',note:'Light layers and everyday programmes.' },
  { key:'festive', label:'Festive / Eid',  note:'Embroidered and occasion programmes, booked early.' },
  { key:'core',    label:'Core / Year-round', note:'Replenishment styles carried all year.' }
];
const CATS = [
  { key:'dresses',     label:'Dresses & Frocks' },
  { key:'tops',        label:'Shirts & Tees' },
  { key:'trousers',    label:'Trousers & Shorts' },
  { key:'sets',        label:'Co-ord & Sets' },
  { key:'outerwear',   label:'Outerwear' },
  { key:'nightwear',   label:'Nightwear' },
  { key:'ethnic',      label:'Ethnic Wear' },
  { key:'accessories', label:'Accessories' }
];
const COLORS = {
  ivory:{n:'Ivory',h:'#F1EADD'}, sand:{n:'Sand',h:'#D8C6A5'}, terracotta:{n:'Terracotta',h:'#B8654A'},
  olive:{n:'Olive',h:'#7D7F5A'}, sage:{n:'Sage',h:'#A3AE95'}, navy:{n:'Navy',h:'#2C3A52'},
  charcoal:{n:'Charcoal',h:'#3A3835'}, rose:{n:'Dusty Rose',h:'#D3A9A0'}, mustard:{n:'Mustard',h:'#C9A04A'},
  denim:{n:'Denim',h:'#6F8BA4'}, brick:{n:'Brick',h:'#9A4331'}, white:{n:'White',h:'#F7F4EF'},
  black:{n:'Black',h:'#22252B'}, sky:{n:'Sky',h:'#8EB8D4'}
};

/* Standard volume breaks. The first band is clipped to the product's MOQ. */
const TIER_BREAKS = [
  { min: 12,  max: 49,  discount: 0 },
  { min: 50,  max: 199, discount: 8 },
  { min: 200, max: 499, discount: 15 },
  { min: 500, max: null, discount: 22 }
];
function tiersFor(moq) {
  const tiers = [];
  let started = false;
  TIER_BREAKS.forEach(t => {
    const top = t.max == null ? Infinity : t.max;
    if (top < moq) return;
    if (!started) { tiers.push({ min: moq, max: t.max, discount: t.discount }); started = true; }
    else tiers.push({ min: t.min, max: t.max, discount: t.discount });
  });
  return tiers;
}
function tierForQty(tiers, qty) {
  return tiers.find(t => qty >= t.min && (t.max == null || qty <= t.max)) || tiers[0];
}
function unitPrice(base, discount) {
  return Math.round(base * (1 - discount / 100));
}

function slugify(s) {
  return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

let _pid = 0;
function P(opts) {
  _pid += 1;
  const age = AGES.find(a => a.key === opts.age_group);
  const sizes = opts.sizes || (age ? age.sizes : []);
  const photo = opts.photo;
  const fx = opts.fx ?? .5, fy = opts.fy ?? .35;
  const moq = opts.moq;
  const images = [
    img(PH[photo], 1000, 1250),
    img(PH[photo], 1000, 1250, { fx, fy, fz: 1.6 }),
    img(PH[photo], 1000, 1250, { fx, fy: fy + .3, fz: 2.1 }),
    img(PH[photo], 1000, 1250, { crop: 'entropy' })
  ];
  const pack = opts.pack_units ? { units: opts.pack_units, ratio: opts.pack_ratio || '' } : null;
  return {
    id: _pid,
    sku: opts.sku,
    name: opts.name,
    slug: opts.slug || slugify(opts.name),
    category: opts.category,
    age_group: opts.age_group,
    season: opts.season,
    fabric: opts.fabric,
    gsm: opts.gsm,
    moq,
    base_price: opts.base_price,
    tiers: tiersFor(moq),
    sizes,
    colors: opts.colors,
    images,
    lead_time_days: opts.lead_time_days,
    stock_status: opts.stock_status || 'in_stock',
    /* helpful extras (still DB-friendly columns) */
    gender: opts.gender || 'unisex',
    description: opts.description,
    tag: opts.tag || '',
    pack_units: opts.pack_units || null,
    pack_ratio: opts.pack_ratio || null,
    pack,
    img: images[0],
    img2: images[1]
  };
}

const PRODUCTS = [
  P({ sku:'NKW-TEE-001', name:'Boxy Cotton Tee', category:'tops', age_group:'kids', season:'summer',
      fabric:'Combed cotton jersey', gsm:180, moq:24, base_price:890, lead_time_days:7,
      colors:['terracotta','ivory','sage'], photo:'red', tag:'Core', fy:.3,
      description:'A relaxed boxy tee in 180 gsm combed cotton jersey. Pre-washed, ribbed crew, built as a replenishment style.' }),
  P({ sku:'NKW-OUT-014', name:'Hooded Rain Mac', category:'outerwear', age_group:'kids', season:'winter',
      fabric:'Coated cotton twill', gsm:220, moq:12, base_price:2490, lead_time_days:14,
      colors:['mustard','navy'], photo:'rain', tag:'Bestseller', fy:.3,
      description:'Water-repellent hooded mac with taped seams, cotton lining and storm cuffs. Packed on a hanger-ready programme.' }),
  P({ sku:'NKW-OUT-022', name:'Utility Field Jacket', category:'outerwear', age_group:'junior', season:'spring',
      fabric:'Washed cotton twill', gsm:280, moq:12, base_price:2890, lead_time_days:14,
      colors:['sand','olive','navy'], photo:'fence', tag:'New', fy:.4,
      description:'Four-pocket field jacket with a corduroy collar. A junior outerwear staple for spring and winter fills.' }),
  P({ sku:'NKW-DRS-008', name:'Garden Smock Dress', category:'dresses', age_group:'kids', season:'spring',
      fabric:'Cotton voile', gsm:120, moq:24, base_price:1990, lead_time_days:10,
      colors:['ivory','rose','sage'], photo:'floral', tag:'New', fy:.3,
      description:'Loose smocked dress in cotton voile with a hand-finished hem. A high-turn spring dress for boutiques.' }),
  P({ sku:'NKW-SET-011', name:'Knit Cardigan & Short Set', category:'sets', age_group:'kids', season:'spring',
      fabric:'Fine-gauge cotton knit', gsm:200, moq:12, base_price:2590, lead_time_days:14,
      colors:['navy','ivory'], photo:'navy', tag:'Bestseller', fy:.3,
      description:'Fine-gauge cardigan with pleated shorts. Sold as a set — a smart programme for school and gatherings.' }),
  P({ sku:'NKW-ETH-031', name:'Printed Lawn Frock', category:'ethnic', age_group:'kids', season:'festive',
      fabric:'Cotton lawn', gsm:80, moq:24, base_price:1890, lead_time_days:12,
      colors:['mustard','rose'], photo:'dresses', tag:'Festive', fy:.35,
      description:'Printed lawn frock with a gathered skirt and contrast piping. Booked as a festive fill-in programme.' }),
  P({ sku:'NKW-BBY-003', name:'Essential Body Suit 3-Pack', category:'sets', age_group:'newborn', season:'core',
      fabric:'Organic cotton jersey', gsm:160, moq:36, base_price:690, lead_time_days:7,
      colors:['ivory','sand','sage'], photo:'body', tag:'Core', pack_units:3, pack_ratio:'3 assorted', fy:.4,
      description:'Envelope-neck body suits in organic cotton. Sold as a pack of 3. A newborn replenishment essential.' }),
  P({ sku:'NKW-OUT-041', name:'Quilted Winter Jacket', category:'outerwear', age_group:'baby', season:'winter',
      fabric:'Quilted cotton / recycled fill', gsm:320, moq:12, base_price:3290, lead_time_days:18,
      colors:['navy','mustard','olive'], photo:'whisper', fy:.45,
      description:'Lightly quilted jacket with a stand collar and snap placket. Packed by size run for winter programmes.' }),
  P({ sku:'NKW-TRS-019', name:'Everyday Jersey Leggings', category:'trousers', age_group:'kids', season:'core',
      fabric:'Cotton elastane jersey', gsm:220, moq:36, base_price:590, lead_time_days:7,
      colors:['navy','charcoal','rose','ivory'], photo:'run', tag:'Core', pack_units:6, pack_ratio:'S-M-L mixed', fy:.8,
      description:'Everyday leggings with a wide waistband. Suggested pack of 6. High-frequency core trouser.' }),
  P({ sku:'NKW-NIT-016', name:'Ribbed Cotton Pyjama Set', category:'nightwear', age_group:'baby', season:'core',
      fabric:'Ribbed cotton jersey', gsm:180, moq:24, base_price:1290, lead_time_days:8,
      colors:['ivory','sage','rose'], photo:'sleep', fy:.35,
      description:'Two-piece ribbed pyjama with a gentle elastic waist. A year-round nightwear programme.' }),
  P({ sku:'NKW-ETH-044', name:'Waistcoat Kurta Set', category:'ethnic', age_group:'kids', season:'festive',
      fabric:'Cotton silk', gsm:140, moq:12, base_price:3990, lead_time_days:21, stock_status:'made_to_order',
      colors:['ivory','navy','sand'], photo:'vest', tag:'Festive', fy:.35,
      description:'Cotton-silk kurta with an embroidered waistcoat and straight pyjama. Made to order for Eid programmes.' }),
  P({ sku:'NKW-ETH-048', name:'Chikankari Kurta Set', category:'ethnic', age_group:'junior', season:'festive',
      fabric:'Cotton voile, chikankari', gsm:90, moq:12, base_price:4290, lead_time_days:21, stock_status:'made_to_order',
      colors:['ivory','rose'], photo:'cam', tag:'Festive', fy:.4,
      description:'Chikankari kurta with trousers and a light dupatta. Hand-finished — book 21 days ahead of the season.' }),
  P({ sku:'NKW-ETH-052', name:'Heritage Shalwar Kameez', category:'ethnic', age_group:'junior', season:'festive',
      fabric:'Wash-and-wear cotton', gsm:160, moq:12, base_price:3490, lead_time_days:16,
      colors:['ivory','sage','sand'], photo:'two', tag:'Festive', fy:.35,
      description:'Classic shalwar kameez with a mandarin collar and contrast placket. A reliable festive staple.' }),
  P({ sku:'NKW-TRS-027', name:'Linen Drawstring Shorts', category:'trousers', age_group:'kids', season:'summer',
      fabric:'Linen-cotton blend', gsm:170, moq:36, base_price:790, lead_time_days:7,
      colors:['sand','sage','terracotta'], photo:'sprink', fy:.8,
      description:'Pull-on linen-blend shorts with a drawstring and side pockets. A summer volume style.' }),
  P({ sku:'NKW-TRS-033', name:'Cotton Twill Chinos', category:'trousers', age_group:'junior', season:'core',
      fabric:'Stretch cotton twill', gsm:240, moq:24, base_price:1390, lead_time_days:10,
      colors:['sand','olive','navy'], photo:'read', fy:.8,
      description:'Slim chinos with an adjustable inner waist. A core trouser for school-age programmes.' }),
  P({ sku:'NKW-SET-018', name:'Sprint Jersey Set', category:'sets', age_group:'kids', season:'summer',
      fabric:'Cotton-rich jersey', gsm:180, moq:24, base_price:1590, lead_time_days:8,
      colors:['mustard','olive'], photo:'run', fy:.4,
      description:'Top-and-short set with flat seams. Packed as a set for summer floors.' }),
  P({ sku:'NKW-TOP-025', name:'Heavyweight Crewneck', category:'tops', age_group:'junior', season:'winter',
      fabric:'Loopback cotton fleece', gsm:380, moq:24, base_price:1790, lead_time_days:12,
      colors:['brick','charcoal','navy'], photo:'whisper', fy:.45,
      description:'380 gsm loopback crew with a ribbed neck and double-stitched seams. A winter fleece programme.' }),
  P({ sku:'NKW-DRS-021', name:'Ruffle Sun Dress', category:'dresses', age_group:'baby', season:'summer',
      fabric:'Printed cotton poplin', gsm:110, moq:24, base_price:1690, lead_time_days:9,
      colors:['mustard','ivory','rose'], photo:'clip', tag:'New', fy:.45,
      description:'Sleeveless printed sun dress with flutter ruffles and a lined skirt. A baby summer bestseller.' }),
  P({ sku:'NKW-ACC-006', name:'Knit Booties & Mitts Set', category:'accessories', age_group:'newborn', season:'winter',
      fabric:'Merino-blend knit', gsm:240, moq:50, base_price:490, lead_time_days:10,
      colors:['ivory','sand'], photo:'feet', pack_units:12, pack_ratio:'12-unit inner', fy:.55,
      description:'Hand-finished booties and mitts. Sold with a 12-unit inner pack. A high-MOQ accessory programme.' }),
  P({ sku:'NKW-TRS-038', name:'Denim Jogger Trousers', category:'trousers', age_group:'kids', season:'spring',
      fabric:'Stretch denim', gsm:260, moq:24, base_price:1290, lead_time_days:10,
      colors:['denim','charcoal'], photo:'denim', fy:.8,
      description:'Soft-washed stretch-denim joggers with a rib cuff. Everyday denim without the hardware fuss.' }),
  P({ sku:'NKW-TOP-012', name:'Washed Denim Shirt', category:'tops', age_group:'junior', season:'spring',
      fabric:'Stonewashed denim', gsm:200, moq:24, base_price:1490, lead_time_days:11,
      colors:['denim'], photo:'dshirt', fy:.45,
      description:'Relaxed denim shirt with pearl snaps. A year-round junior shirt programme.' }),
  P({ sku:'NKW-NIT-028', name:'Printed Jersey Night Suit', category:'nightwear', age_group:'kids', season:'core',
      fabric:'Cotton jersey', gsm:160, moq:36, base_price:1190, lead_time_days:8,
      colors:['sky','mustard','ivory'], photo:'sleep', pack_units:6, pack_ratio:'6 mixed prints', fy:.35,
      description:'Printed night suit in breathable jersey. Suggested pack of 6 mixed prints.' }),
  P({ sku:'NKW-DRS-015', name:'Pleated Pinafore', category:'dresses', age_group:'kids', season:'winter',
      fabric:'Cotton twill', gsm:200, moq:24, base_price:1890, lead_time_days:10,
      colors:['navy','olive'], photo:'cam', fy:.45,
      description:'Twill pinafore with a pleated skirt and adjustable straps. Layers over tees through the year.' })
];

const FABRICS = [...new Set(PRODUCTS.map(p => p.fabric))].sort();
const MOQ_OPTIONS = [...new Set(PRODUCTS.map(p => p.moq))].sort((a, b) => a - b);

const byId = id => PRODUCTS.find(p => p.id === Number(id));
const bySku = sku => PRODUCTS.find(p => p.sku === sku);
const bySlug = slug => PRODUCTS.find(p => p.slug === slug);

const SIZE_GUIDE = {
  newborn: [['0 – 3 m','56–62','41','40'],['3 – 6 m','62–68','43','42'],['6 – 12 m','68–80','46','44']],
  baby:    [['12 – 18 m','80–86','48','46'],['18 – 24 m','86–92','49','47'],['2 – 3 y','92–98','52','49']],
  kids:    [['4 – 5 y','104–110','56','52'],['6 – 7 y','116–122','60','54'],['7 – 8 y','122–128','63','56']],
  junior:  [['9 – 10 y','134–140','68','59'],['11 – 12 y','146–152','74','63'],['13 – 14 y','158–164','80','67']]
};

const ACCOUNT_MANAGER = {
  name: 'Ayesha Malik',
  role: 'Trade account manager',
  phone: '+92 300 855 2140',
  email: 'ayesha.malik@nanokids.pk',
  hours: 'Mon – Sat, 10 am – 6 pm'
};

const TRADE_CONTACT = {
  email: 'trade@nanokids.pk',
  phone: '+92 42 3577 6400',
  whatsapp: '+92 300 855 2140',
  warehouse: '14-A, Quaid-e-Azam Industrial Estate, Kot Lakhpat, Lahore',
  showroom: '12-B, Main Boulevard, Gulberg III, Lahore'
};
