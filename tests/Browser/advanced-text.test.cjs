const {test} = require('node:test');
const assert = require('node:assert/strict');
const {readFileSync} = require('node:fs');
const {resolve} = require('node:path');
function editor(value, overrides = {}, fetch = async () => ({ok:true,json: async () => ({})}), clipboard = async () => {}) {
    const view = readFileSync(resolve(__dirname, '../../resources/views/advanced-text.blade.php'), 'utf8');
    const config = {prefix:'',suffix:'',prefixConcatenate:true,suffixConcatenate:true,mapping:{},suggestions:[],remoteUrl:null,allowedUrls:[],readonly:false,...overrides};
    const messages = {unlicensed_source:'Not allowed',load_failed:'Load failed',copy_failed:'Copy failed',copied:'Copied',copy_content:'Copy content',copy:'Copy'};
    const expression = view.match(/<div x-data="([\s\S]*?)" class=/)[1]
        .replace('@js($getStatePath())', "'value'").replace('@js($getAdvancedTextConfig())',JSON.stringify(config))
        .replace(/@js\(__\('filament-advanced-text::messages\.([a-z_]+)'\)\)/g, (_, key) => JSON.stringify(messages[key]));
    const form = new Function('$wire','window','fetch','AbortController','navigator',`return (${expression})`)({$entangle: () => value},{location:{origin:'https://cms.test'}},fetch,AbortController,{clipboard:{writeText:clipboard}});
    form.init(); return form;
}
test('affixes round trip without duplicates and mapping supports zero and false', () => {
    const form = editor('SKU-42-STD',{prefix:'SKU-',suffix:'-STD',mapping:{零:0,否:false}});
    assert.equal(form.core(),'42');
    form.input('零'); assert.equal(form.state,'SKU-0-STD');
    form.input('否'); assert.equal(form.state,'SKU-false-STD');
    form.input(''); assert.equal(form.state,'');
    const visual = editor(null,{prefix:'¥',prefixConcatenate:false});
    visual.input('0'); assert.equal(visual.state,'0');
});
test('remote suggestions use query and session credentials without changing input and ignore stale responses', async () => {
    let deliver;
    const requests = [];
    const form = editor('',{remoteUrl:'/suggest',mapping:{固定:'/fixed'}},async (url,options) => {requests.push({url,options}); return {ok:true,json: () => new Promise(resolve => {deliver=resolve;})};});
    form.input('旧'); const pending = form.search('旧');
    await Promise.resolve();
    form.input('新'); deliver({旧结果:'/old'}); await pending;
    assert.equal(form.state,'新'); assert.deepEqual(form.labels(),['固定']);
    assert.equal(new URL(requests[0].url).searchParams.get('query'),'旧');
    assert.equal(requests[0].options.credentials,'same-origin');
    assert.equal(requests[0].options.signal.aborted,true);
});
test('failed or unapproved remote sources retain manual entry and static suggestions', async () => {
    let requests = 0;
    const form = editor('',{remoteUrl:'https://outside.test/suggest',suggestions:['静态']},async () => {requests++; throw new Error();});
    form.input('输入'); await form.search('输入');
    assert.equal(requests,0); assert.equal(form.state,'输入'); assert.deepEqual(form.labels(),['静态']);
    form.config.remoteUrl='/failed'; await form.search('输入');
    assert.equal(requests,1); assert.ok(form.error); assert.equal(form.state,'输入');
});
test('successful remote mapping merges static entries and selection saves actual value', async () => {
    const form = editor('',{remoteUrl:'/suggest',mapping:{静态:'/static'}},async () => ({ok:true,json:async()=>({首页:'/home',零:0})}));
    form.input('首'); await form.search('首');
    assert.equal(form.state,'首');
    assert.deepEqual(form.labels(),['静态','首页','零']);
    form.input('首页'); assert.equal(form.state,'/home');
});

test('copy uses the final composed value and readonly input cannot change state', async () => {
    let copied;
    const form = editor('42',{prefix:'SKU-',suffix:'元',suffixConcatenate:false}, undefined, async value => {copied=value;});
    await form.copy();
    assert.equal(copied,'SKU-42'); assert.equal(form.copied,true);
    form.config.readonly = true; form.input('forged'); assert.equal(form.state,'42');
});
test('length enforcement counts Unicode characters consistently with the PHP validator', () => {
    const form = editor('', {maxLength:2,prefix:'SKU-'});
    form.input('😀😀字');
    assert.equal(form.state, 'SKU-😀😀');
    assert.equal(Array.from(form.core()).length, 2);
});
test('input composition is not truncated until committed and the visible value matches the stored core', () => {
    const form = editor('中文',{maxLength:2});
    const target = {value:'中文拼音'};
    form.handleInput({target,isComposing:true});
    assert.equal(target.value,'中文拼音'); assert.equal(form.state,'中文');
    form.handleInput({target,isComposing:false});
    assert.equal(target.value,'中文'); assert.equal(form.state,'中文');
});
