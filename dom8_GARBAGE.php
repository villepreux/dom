<?php

namespace dom;
require_once(__DIR__."/dom8.php");

function css_reset_new_reset($layer = "reset.new-reset")
{
    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

        /***
            The new CSS reset - version 1.11.3 (last updated 25.08.2024)
            GitHub page: https://github.com/elad2412/the-new-css-reset
        ***/

        /*
            Remove all the styles of the "User-Agent-Stylesheet", except for the 'display' property
            - The "symbol *" part is to solve Firefox SVG sprite bug
            - The "html" element is excluded, otherwise a bug in Chrome breaks the CSS hyphens property (https://github.com/elad2412/the-new-css-reset/issues/36)
        */
        *:where(:not(html, iframe, canvas, img, svg, video, audio):not(svg *, symbol *)) {
            all: unset;
            display: revert;
        }

        /* Preferred box-sizing value */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        /* Fix mobile Safari increase font-size on landscape mode */
        html {
            -moz-text-size-adjust: none;
            -webkit-text-size-adjust: none;
            text-size-adjust: none;
        }

        /* Reapply the pointer cursor for anchor tags */
        a, button {
            cursor: revert;
        }

        /* Remove list styles (bullets/numbers) */
        ol, ul, menu, summary {
            list-style: none;
        }

        /* Firefox: solve issue where nested ordered lists continue numbering from parent (https://bugzilla.mozilla.org/show_bug.cgi?id=1881517) */
        ol {
            counter-reset: revert;
        }

        /* For images to not be able to exceed their container */
        img {
            max-inline-size: 100%;
            max-block-size: 100%;
        }

        /* removes spacing between cells in tables */
        table {
            border-collapse: collapse;
        }

        /* Safari - solving issue when using user-select:none on the <body> text input doesn't working */
        input, textarea {
            user-select: auto;
            -webkit-user-select: auto;
        }

        /* revert the 'white-space' property for textarea elements on Safari */
        textarea {
            white-space: revert;
        }

        /* minimum style to allow to style meter element */
        meter {
            -webkit-appearance: revert;
            appearance: revert;
        }

        /* preformatted text - use only for this feature */
        :where(pre) {
            all: revert;
            box-sizing: border-box;
        }

        /* reset default text opacity of input placeholder */
        ::placeholder {
            color: unset;
        }

        /* fix the feature of 'hidden' attribute.
        display:revert; revert to element instead of attribute */
        :where([hidden]) {
            display: none;
        }

        /* revert for bug in Chromium browsers
        - fix for the content editable attribute will work properly.
        - webkit-user-select: auto; added for Safari in case of using user-select:none on wrapper element*/
        :where([contenteditable]:not([contenteditable="false"])) {
            -moz-user-modify: read-write;
            -webkit-user-modify: read-write;
            overflow-wrap: break-word;
            line-break: after-white-space;
            -webkit-line-break: after-white-space;
            user-select: auto;
            -webkit-user-select: auto;
        }

        /* apply back the draggable feature - exist only in Chromium and Safari */
        :where([draggable="true"]) {
            -webkit-user-drag: element;
        }

        /* Revert Modal native behavior */
        :where(dialog:modal) {
            all: revert;
            box-sizing: border-box;
        }

        /* Remove details summary webkit styles */
        ::-webkit-details-marker {
            display: none;
        }

    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}

function css_reset($layer = "reset")
{
    return css_reset_new_reset("new-reset");
}

function css_normalize_remedy_quotes($layer = "quotes")
{
    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

        /* @docs
        label: Quotes
        version: 0.1.0-beta.2

        note: |
        This is what user agents are supposed to be doing for quotes,
        according to https://html.spec.whatwg.org/multipage/rendering.html#quotes

        links:
        - https://html.spec.whatwg.org/multipage/rendering.html#quotes

        todo: |
        I believe

        ```css
        :root:lang(af),       :not(:lang(af)) > :lang(af)             { quotes: '\201c' '\201d' '\2018' '\2019' }
        :root:lang(ak),       :not(:lang(ak)) > :lang(ak)             { quotes: '\201c' '\201d' '\2018' '\2019' }
        :root:lang(asa),      :not(:lang(asa)) > :lang(asa)           { quotes: '\201c' '\201d' '\2018' '\2019' }
        ```

        can be replaced by

        ```css
        :root:lang(af, ak, asa), [lang]:lang(af, ak, asa)       			{ quotes: '\201c' '\201d' '\2018' '\2019' }
        ```

        category: file
        */

        :root                                                         { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(af),       :not(:lang(af)) > :lang(af)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ak),       :not(:lang(ak)) > :lang(ak)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(asa),      :not(:lang(asa)) > :lang(asa)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(az),       :not(:lang(az)) > :lang(az)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(bem),      :not(:lang(bem)) > :lang(bem)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(bez),      :not(:lang(bez)) > :lang(bez)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(bn),       :not(:lang(bn)) > :lang(bn)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(brx),      :not(:lang(brx)) > :lang(brx)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(cgg),      :not(:lang(cgg)) > :lang(cgg)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(chr),      :not(:lang(chr)) > :lang(chr)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(cy),       :not(:lang(cy)) > :lang(cy)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(da),       :not(:lang(da)) > :lang(da)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(dav),      :not(:lang(dav)) > :lang(dav)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(dje),      :not(:lang(dje)) > :lang(dje)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(dz),       :not(:lang(dz)) > :lang(dz)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ebu),      :not(:lang(ebu)) > :lang(ebu)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ee),       :not(:lang(ee)) > :lang(ee)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(en),       :not(:lang(en)) > :lang(en)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(fil),      :not(:lang(fil)) > :lang(fil)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(fo),       :not(:lang(fo)) > :lang(fo)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ga),       :not(:lang(ga)) > :lang(ga)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(gd),       :not(:lang(gd)) > :lang(gd)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(gl),       :not(:lang(gl)) > :lang(gl)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(gu),       :not(:lang(gu)) > :lang(gu)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(guz),      :not(:lang(guz)) > :lang(guz)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ha),       :not(:lang(ha)) > :lang(ha)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(hi),       :not(:lang(hi)) > :lang(hi)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(id),       :not(:lang(id)) > :lang(id)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ig),       :not(:lang(ig)) > :lang(ig)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(jmc),      :not(:lang(jmc)) > :lang(jmc)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(kam),      :not(:lang(kam)) > :lang(kam)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(kde),      :not(:lang(kde)) > :lang(kde)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(kea),      :not(:lang(kea)) > :lang(kea)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(khq),      :not(:lang(khq)) > :lang(khq)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ki),       :not(:lang(ki)) > :lang(ki)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(kln),      :not(:lang(kln)) > :lang(kln)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(km),       :not(:lang(km)) > :lang(km)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(kn),       :not(:lang(kn)) > :lang(kn)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ko),       :not(:lang(ko)) > :lang(ko)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ksb),      :not(:lang(ksb)) > :lang(ksb)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(lg),       :not(:lang(lg)) > :lang(lg)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ln),       :not(:lang(ln)) > :lang(ln)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(lo),       :not(:lang(lo)) > :lang(lo)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(lrc),      :not(:lang(lrc)) > :lang(lrc)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(lu),       :not(:lang(lu)) > :lang(lu)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(luo),      :not(:lang(luo)) > :lang(luo)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(lv),       :not(:lang(lv)) > :lang(lv)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mas),      :not(:lang(mas)) > :lang(mas)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mer),      :not(:lang(mer)) > :lang(mer)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mfe),      :not(:lang(mfe)) > :lang(mfe)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mgo),      :not(:lang(mgo)) > :lang(mgo)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ml),       :not(:lang(ml)) > :lang(ml)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mn),       :not(:lang(mn)) > :lang(mn)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mr),       :not(:lang(mr)) > :lang(mr)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ms),       :not(:lang(ms)) > :lang(ms)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(mt),       :not(:lang(mt)) > :lang(mt)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(my),       :not(:lang(my)) > :lang(my)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(naq),      :not(:lang(naq)) > :lang(naq)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(nd),       :not(:lang(nd)) > :lang(nd)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ne),       :not(:lang(ne)) > :lang(ne)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(nus),      :not(:lang(nus)) > :lang(nus)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(nyn),      :not(:lang(nyn)) > :lang(nyn)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(pa),       :not(:lang(pa)) > :lang(pa)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(pt),       :not(:lang(pt)) > :lang(pt)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(rof),      :not(:lang(rof)) > :lang(rof)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(rwk),      :not(:lang(rwk)) > :lang(rwk)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(saq),      :not(:lang(saq)) > :lang(saq)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(sbp),      :not(:lang(sbp)) > :lang(sbp)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(seh),      :not(:lang(seh)) > :lang(seh)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ses),      :not(:lang(ses)) > :lang(ses)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(si),       :not(:lang(si)) > :lang(si)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(so),       :not(:lang(so)) > :lang(so)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(sw),       :not(:lang(sw)) > :lang(sw)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(ta),       :not(:lang(ta)) > :lang(ta)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(te),       :not(:lang(te)) > :lang(te)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(teo),      :not(:lang(teo)) > :lang(teo)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(th),       :not(:lang(th)) > :lang(th)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(to),       :not(:lang(to)) > :lang(to)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(tr),       :not(:lang(tr)) > :lang(tr)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(twq),      :not(:lang(twq)) > :lang(twq)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(tzm),      :not(:lang(tzm)) > :lang(tzm)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(uz-Cyrl),  :not(:lang(uz-Cyrl)) > :lang(uz-Cyrl)   { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(vai),      :not(:lang(vai)) > :lang(vai)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(vai-Latn), :not(:lang(vai-Latn)) > :lang(vai-Latn) { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(vi),       :not(:lang(vi)) > :lang(vi)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(vun),      :not(:lang(vun)) > :lang(vun)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(xog),      :not(:lang(xog)) > :lang(xog)           { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(yo),       :not(:lang(yo)) > :lang(yo)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(yue-Hans), :not(:lang(yue-Hans)) > :lang(yue-Hans) { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(zh),       :not(:lang(zh)) > :lang(zh)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */
        :root:lang(zu),       :not(:lang(zu)) > :lang(zu)             { quotes: '\201c' '\201d' '\2018' '\2019' } /* “ ” ‘ ’ */


        :root:lang(uz),       :not(:lang(uz)) > :lang(uz)             { quotes: '\201c' '\201d' '\2019' '\2018' } /* “ ” ’ ‘ */


        :root:lang(eu),       :not(:lang(eu)) > :lang(eu)             { quotes: '\201c' '\201d' '\201c' '\201d' } /* “ ” “ ” */
        :root:lang(tk),       :not(:lang(tk)) > :lang(tk)             { quotes: '\201c' '\201d' '\201c' '\201d' } /* “ ” “ ” */


        :root:lang(ar),       :not(:lang(ar)) > :lang(ar)             { quotes: '\201d' '\201c' '\2019' '\2018' } /* ” “ ’ ‘ */
        :root:lang(ur),       :not(:lang(ur)) > :lang(ur)             { quotes: '\201d' '\201c' '\2019' '\2018' } /* ” “ ’ ‘ */


        :root:lang(fi),       :not(:lang(fi)) > :lang(fi)             { quotes: '\201d' '\201d' '\2019' '\2019' } /* ” ” ’ ’ */
        :root:lang(he),       :not(:lang(he)) > :lang(he)             { quotes: '\201d' '\201d' '\2019' '\2019' } /* ” ” ’ ’ */
        :root:lang(lag),      :not(:lang(lag)) > :lang(lag)           { quotes: '\201d' '\201d' '\2019' '\2019' } /* ” ” ’ ’ */
        :root:lang(rn),       :not(:lang(rn)) > :lang(rn)             { quotes: '\201d' '\201d' '\2019' '\2019' } /* ” ” ’ ’ */
        :root:lang(sn),       :not(:lang(sn)) > :lang(sn)             { quotes: '\201d' '\201d' '\2019' '\2019' } /* ” ” ’ ’ */
        :root:lang(sv),       :not(:lang(sv)) > :lang(sv)             { quotes: '\201d' '\201d' '\2019' '\2019' } /* ” ” ’ ’ */


        :root:lang(sr),       :not(:lang(sr)) > :lang(sr)             { quotes: '\201e' '\201c' '\2018' '\2018' } /* „ “ ‘ ‘ */
        :root:lang(sr-Latn),  :not(:lang(sr-Latn)) > :lang(sr-Latn)   { quotes: '\201e' '\201c' '\2018' '\2018' } /* „ “ ‘ ‘ */


        :root:lang(bg),       :not(:lang(bg)) > :lang(bg)             { quotes: '\201e' '\201c' '\201e' '\201c' } /* „ “ „ “ */
        :root:lang(lt),       :not(:lang(lt)) > :lang(lt)             { quotes: '\201e' '\201c' '\201e' '\201c' } /* „ “ „ “ */


        :root:lang(bs-Cyrl),  :not(:lang(bs-Cyrl)) > :lang(bs-Cyrl)   { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(cs),       :not(:lang(cs)) > :lang(cs)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(cs),       :not(:lang(cs)) > :lang(cs)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(de),       :not(:lang(de)) > :lang(de)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(dsb),      :not(:lang(dsb)) > :lang(dsb)           { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(et),       :not(:lang(et)) > :lang(et)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(hr),       :not(:lang(hr)) > :lang(hr)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(hsb),      :not(:lang(hsb)) > :lang(hsb)           { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(is),       :not(:lang(is)) > :lang(is)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(lb),       :not(:lang(lb)) > :lang(lb)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(luy),      :not(:lang(luy)) > :lang(luy)           { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(mk),       :not(:lang(mk)) > :lang(mk)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(sk),       :not(:lang(sk)) > :lang(sk)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */
        :root:lang(sl),       :not(:lang(sl)) > :lang(sl)             { quotes: '\201e' '\201c' '\201a' '\2018' } /* „ “ ‚ ‘ */


        :root:lang(ka),       :not(:lang(ka)) > :lang(ka)             { quotes: '\201e' '\201c' '\00ab' '\00bb' } /* „ “ « » */


        :root:lang(bs),       :not(:lang(bs)) > :lang(bs)             { quotes: '\201e' '\201d' '\2018' '\2019' } /* „ ” ‘ ’ */


        :root:lang(agq),      :not(:lang(agq)) > :lang(agq)           { quotes: '\201e' '\201d' '\201a' '\2019' } /* „ ” ‚ ’ */
        :root:lang(ff),       :not(:lang(ff)) > :lang(ff)             { quotes: '\201e' '\201d' '\201a' '\2019' } /* „ ” ‚ ’ */


        :root:lang(nmg),      :not(:lang(nmg)) > :lang(nmg)           { quotes: '\201e' '\201d' '\00ab' '\00bb' } /* „ ” « » */
        :root:lang(ro),       :not(:lang(ro)) > :lang(ro)             { quotes: '\201e' '\201d' '\00ab' '\00bb' } /* „ ” « » */
        :root:lang(pl),       :not(:lang(pl)) > :lang(pl)             { quotes: '\201e' '\201d' '\00ab' '\00bb' } /* „ ” « » */


        :root:lang(hu),       :not(:lang(hu)) > :lang(hu)             { quotes: '\201e' '\201d' '\00bb' '\00ab' } /* „ ” » « */


        :root:lang(nl),       :not(:lang(nl)) > :lang(nl)             { quotes: '\2018' '\2019' '\201c' '\201d' } /* ‘ ’ “ ” */
        :root:lang(ti-ER),    :not(:lang(ti-ER)) > :lang(ti-ER)       { quotes: '\2018' '\2019' '\201c' '\201d' } /* ‘ ’ “ ” */


        :root:lang(dua),      :not(:lang(dua)) > :lang(dua)           { quotes: '\00ab' '\00bb' '\2018' '\2019' } /* « » ‘ ’ */
        :root:lang(ksf),      :not(:lang(ksf)) > :lang(ksf)           { quotes: '\00ab' '\00bb' '\2018' '\2019' } /* « » ‘ ’ */
        :root:lang(rw),       :not(:lang(rw)) > :lang(rw)             { quotes: '\00ab' '\00bb' '\2018' '\2019' } /* « » ‘ ’ */
        :root:lang(nn),       :not(:lang(nn)) > :lang(nn)             { quotes: '\00ab' '\00bb' '\2018' '\2019' } /* « » ‘ ’ */
        :root:lang(nb),       :not(:lang(nb)) > :lang(nb)             { quotes: '\00ab' '\00bb' '\2018' '\2019' } /* « » ‘ ’ */


        :root:lang(ast),      :not(:lang(ast)) > :lang(ast)           { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(bm),       :not(:lang(bm)) > :lang(bm)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(br),       :not(:lang(br)) > :lang(br)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(ca),       :not(:lang(ca)) > :lang(ca)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(dyo),      :not(:lang(dyo)) > :lang(dyo)           { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(el),       :not(:lang(el)) > :lang(el)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(es),       :not(:lang(es)) > :lang(es)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(ewo),      :not(:lang(ewo)) > :lang(ewo)           { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(mg),       :not(:lang(mg)) > :lang(mg)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(mua),      :not(:lang(mua)) > :lang(mua)           { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(sg),       :not(:lang(sg)) > :lang(sg)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(it),       :not(:lang(it)) > :lang(it)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(kab),      :not(:lang(kab)) > :lang(kab)           { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(kk),       :not(:lang(kk)) > :lang(kk)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(pt-PT),    :not(:lang(pt-PT)) > :lang(pt-PT)       { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(nnh),      :not(:lang(nnh)) > :lang(nnh)           { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */
        :root:lang(sq),       :not(:lang(sq)) > :lang(sq)             { quotes: '\00ab' '\00bb' '\201c' '\201d' } /* « » “ ” */


        :root:lang(bas),      :not(:lang(bas)) > :lang(bas)           { quotes: '\00ab' '\00bb' '\201e' '\201c' } /* « » „ “ */
        :root:lang(be),       :not(:lang(be)) > :lang(be)             { quotes: '\00ab' '\00bb' '\201e' '\201c' } /* « » „ “ */
        :root:lang(ky),       :not(:lang(ky)) > :lang(ky)             { quotes: '\00ab' '\00bb' '\201e' '\201c' } /* « » „ “ */
        :root:lang(sah),      :not(:lang(sah)) > :lang(sah)           { quotes: '\00ab' '\00bb' '\201e' '\201c' } /* « » „ “ */
        :root:lang(ru),       :not(:lang(ru)) > :lang(ru)             { quotes: '\00ab' '\00bb' '\201e' '\201c' } /* « » „ “ */
        :root:lang(uk),       :not(:lang(uk)) > :lang(uk)             { quotes: '\00ab' '\00bb' '\201e' '\201c' } /* « » „ “ */


        :root:lang(zgh),      :not(:lang(zgh)) > :lang(zgh)           { quotes: '\00ab' '\00bb' '\201e' '\201d' } /* « » „ ” */
        :root:lang(shi),      :not(:lang(shi)) > :lang(shi)           { quotes: '\00ab' '\00bb' '\201e' '\201d' } /* « » „ ” */
        :root:lang(shi-Latn), :not(:lang(shi-Latn)) > :lang(shi-Latn) { quotes: '\00ab' '\00bb' '\201e' '\201d' } /* « » „ ” */


        :root:lang(am),       :not(:lang(am)) > :lang(am)             { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(az-Cyrl),  :not(:lang(az-Cyrl)) > :lang(az-Cyrl)   { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(fa),       :not(:lang(fa)) > :lang(fa)             { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(fr-CH),    :not(:lang(fr-CH)) > :lang(fr-CH)       { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(gsw),      :not(:lang(gsw)) > :lang(gsw)           { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(jgo),      :not(:lang(jgo)) > :lang(jgo)           { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(kkj),      :not(:lang(kkj)) > :lang(kkj)           { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */
        :root:lang(mzn),      :not(:lang(mzn)) > :lang(mzn)           { quotes: '\00ab' '\00bb' '\2039' '\203a' } /* « » ‹ › */


        :root:lang(fr),       :not(:lang(fr)) > :lang(fr)             { quotes: '\00ab' '\00bb' '\00ab' '\00bb' } /* « » « » */
        :root:lang(hy),       :not(:lang(hy)) > :lang(hy)             { quotes: '\00ab' '\00bb' '\00ab' '\00bb' } /* « » « » */
        :root:lang(yav),      :not(:lang(yav)) > :lang(yav)           { quotes: '\00ab' '\00bb' '\00ab' '\00bb' } /* « » « » */


        :root:lang(ja),       :not(:lang(ja)) > :lang(ja)             { quotes: '\300c' '\300d' '\300e' '\300f' } /* 「 」 『 』 */
        :root:lang(yue),      :not(:lang(yue)) > :lang(yue)           { quotes: '\300c' '\300d' '\300e' '\300f' } /* 「 」 『 』 */
        :root:lang(zh-Hant),  :not(:lang(zh-Hant)) > :lang(zh-Hant)   { quotes: '\300c' '\300d' '\300e' '\300f' } /* 「 」 『 』 */


    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}

function css_normalize_remedy_reminders($layer = "reminders")
{
    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}

function css_normalize_normalize($layer = "normalize")
{
    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 
        
        /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */
        
        /* Document
         ========================================================================== */
        
        /**
         * 1. Correct the line height in all browsers.
         * 2. Prevent adjustments of font size after orientation changes in iOS.
         */
        
        html {
            line-height: 1.15; /* 1 */
            -webkit-text-size-adjust: 100%; /* 2 */
        }
        
        /* Sections
        ========================================================================== */
        
        /**
         * Remove the margin in all browsers.
         */            
        body {
            margin: 0;
        }
        
        /**
         * Render the `main` element consistently in IE.
         */            
        main {
            display: block;
        }
        
        /**
        * Correct the font size and margin on `h1` elements within `section` and
        * `article` contexts in Chrome, Firefox, and Safari.
        */
        
        h1 {
        font-size: 2em;
        margin: 0.67em 0;
        }
        
        /* Grouping content
        ========================================================================== */
        
        /**
        * 1. Add the correct box sizing in Firefox.
        * 2. Show the overflow in Edge and IE.
        */
        
        hr {
        box-sizing: content-box; /* 1 */
        height: 0; /* 1 */
        overflow: visible; /* 2 */
        }
        
        /**
        * 1. Correct the inheritance and scaling of font size in all browsers.
        * 2. Correct the odd `em` font sizing in all browsers.
        */
        
        pre {
        font-family: monospace, monospace; /* 1 */
        font-size: 1em; /* 2 */
        }
        
        /* Text-level semantics
        ========================================================================== */
        
        /**
        * Remove the gray background on active links in IE 10.
        */
        
        a {
        background-color: transparent;
        }
        
        /**
        * 1. Remove the bottom border in Chrome 57-
        * 2. Add the correct text decoration in Chrome, Edge, IE, Opera, and Safari.
        */
        
        abbr[title] {
        border-bottom: none; /* 1 */
        text-decoration: underline; /* 2 */
        text-decoration: underline dotted; /* 2 */
        }
        
        /**
        * Add the correct font weight in Chrome, Edge, and Safari.
        */
        
        b,
        strong {
        font-weight: bolder;
        }
        
        /**
        * 1. Correct the inheritance and scaling of font size in all browsers.
        * 2. Correct the odd `em` font sizing in all browsers.
        */
        
        code,
        kbd,
        samp {
        font-family: monospace, monospace; /* 1 */
        font-size: 1em; /* 2 */
        }
        
        /**
        * Add the correct font size in all browsers.
        */
        
        small {
        font-size: 80%;
        }
        
        /**
        * Prevent `sub` and `sup` elements from affecting the line height in
        * all browsers.
        */
        
        sub,
        sup {
        font-size: 75%;
        line-height: 0;
        position: relative;
        vertical-align: baseline;
        }
        
        sub {
        bottom: -0.25em;
        }
        
        sup {
        top: -0.5em;
        }
        
        /* Embedded content
        ========================================================================== */
        
        /**
        * Remove the border on images inside links in IE 10.
        */
        
        img {
        border-style: none;
        }
        
        /* Forms
        ========================================================================== */
        
        /**
        * 1. Change the font styles in all browsers.
        * 2. Remove the margin in Firefox and Safari.
        */
        
        button:not(.transparent),
        input,
        optgroup,
        select,
        textarea {
        font-family: inherit; /* 1 */
        font-size: 100%; /* 1 */
        line-height: 1.15; /* 1 */
        margin: 0; /* 2 */
        }
        
        /**
        * Show the overflow in IE.
        * 1. Show the overflow in Edge.
        */
        
        button,
        input { /* 1 */
        overflow: visible;
        }
        
        /**
        * Remove the inheritance of text transform in Edge, Firefox, and IE.
        * 1. Remove the inheritance of text transform in Firefox.
        */
        
        button,
        select { /* 1 */
        text-transform: none;
        }
        
        /**
        * Correct the inability to style clickable types in iOS and Safari.
        */
        
        button:not(.transparent),
        [type="button"]:not(.transparent),
        [type="reset"]:not(.transparent),
        [type="submit"]:not(.transparent) {
        -webkit-appearance: button;
        appearance: button;/* added by DOM */
        }
        
        /**
        * Remove the inner border and padding in Firefox.
        */
        
        button::-moz-focus-inner,
        [type="button"]::-moz-focus-inner,
        [type="reset"]::-moz-focus-inner,
        [type="submit"]::-moz-focus-inner {
        border-style: none;
        padding: 0;
        }
        
        /**
        * Restore the focus styles unset by the previous rule.
        */
        
        button:-moz-focusring,
        [type="button"]:-moz-focusring,
        [type="reset"]:-moz-focusring,
        [type="submit"]:-moz-focusring {
        outline: 1px dotted ButtonText;
        }
        
        /**
        * Correct the padding in Firefox.
        */
        
        fieldset {
        padding: 0.35em 0.75em 0.625em;
        }
        
        /**
        * 1. Correct the text wrapping in Edge and IE.
        * 2. Correct the color inheritance from `fieldset` elements in IE.
        * 3. Remove the padding so developers are not caught out when they zero out
        *    `fieldset` elements in all browsers.
        */
        
        legend {
        box-sizing: border-box; /* 1 */
        color: inherit; /* 2 */
        display: table; /* 1 */
        max-width: 100%; /* 1 */
        padding: 0; /* 3 */
        white-space: normal; /* 1 */
        }
        
        /**
        * Add the correct vertical alignment in Chrome, Firefox, and Opera.
        */
        
        progress {
        vertical-align: baseline;
        }
        
        /**
        * Remove the default vertical scrollbar in IE 10+.
        */
        
        textarea {
        overflow: auto;
        }
        
        /**
        * 1. Add the correct box sizing in IE 10.
        * 2. Remove the padding in IE 10.
        */
        
        [type="checkbox"],
        [type="radio"] {
        box-sizing: border-box; /* 1 */
        padding: 0; /* 2 */
        }
        
        /**
        * Correct the cursor style of increment and decrement buttons in Chrome.
        */
        
        [type="number"]::-webkit-inner-spin-button,
        [type="number"]::-webkit-outer-spin-button {
        height: auto;
        }
        
        /**
        * 1. Correct the odd appearance in Chrome and Safari.
        * 2. Correct the outline style in Safari.
        */
        
        [type="search"] {
        -webkit-appearance: textfield; /* 1 */
        appearance: textfield; /* added by DOM */
        outline-offset: -2px; /* 2 */
        }
        
        /**
        * Remove the inner padding in Chrome and Safari on macOS.
        */
        
        [type="search"]::-webkit-search-decoration {
        -webkit-appearance: none;
        appearance: none; /* added by DOM */
        }
        
        /**
        * 1. Correct the inability to style clickable types in iOS and Safari.
        * 2. Change font properties to `inherit` in Safari.
        */
        
        ::-webkit-file-upload-button {
        -webkit-appearance: button; /* 1 */
        appearance: button; /* added by DOM */
        font: inherit; /* 2 */
        }
        
        /* Interactive
        ========================================================================== */
        
        /*
        * Add the correct display in Edge, IE 10+, and Firefox.
        */
        
        details {
            display: block;
        }
        
        /*
        * Add the correct display in all browsers.
        */
        
        summary {
            display: list-item;
        }
        
        /* Misc
        ========================================================================== */
        
        /**
        * Add the correct display in IE 10+.
        */
        
        template {
            display: none;
        }
        
        /**
        * Add the correct display in IE 10.
        */
        
        [hidden] {
            display: none;
        }

    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}



// TOOLBAR


function css_vars_color_scheme_light_brands_toolbar($current_selector = ":root")
{
    if (has("dom_toolbar_no_css")) return "";
    
    heredoc_start(-2); ?><style>:root {<?php heredoc_flush(null); ?> 

        } .toolbar-row, .toolbar-row * {

        --warning-disable: do not use empty rulesets;

        <?= brand_color_css_property("darkandlight", "#dddddd", 35, "light") ?> 

        } <?= $current_selector ?> {

    <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
}

function css_vars_color_scheme_dark_brands_toolbar($current_selector = ":root")
{
    if (has("dom_toolbar_no_css")) return "";
    
    heredoc_start(-2); ?><style>:root {<?php heredoc_flush(null); ?> 

        } .toolbar-row, .toolbar-row * {

        --warning-disable: do not use empty rulesets;

        <?= brand_color_css_property("darkandlight", "#222222", 35, "dark") ?> 

        } <?= $current_selector ?> {

    <?php heredoc_flush("raw_css"); ?>}</style><?php return heredoc_stop(null);
}