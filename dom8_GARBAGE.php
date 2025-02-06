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

function css_base_layout($layer = "base")
{
    $grid = ":is(.grid, *:has(> .card + .card))";

    heredoc_start(-2); ?><style><?php heredoc_flush(null); ?> 

        @property --scrollbar-width {
            syntax: "<length>";
            inherits: true;
            initial-value: 0px; 
        }

        html {

            container-type: size;
        }

        /* My own base/remedy css  */

        :root {
            /*
            --root-font-size:           clamp(1.00rem, 0.59rem + 1.47vw, 1.25rem);
            --line-height:              clamp(1.35rem, 1.60rem + 1.70vw, 1.50rem);*/

            --root-font-size:           <?= css_clamp(16.0, 20, 600, 1200, 16) ?>;
            --line-height:              <?= css_clamp(21.6, 24, 600, 1200, 16) ?>;

            --max-text-width:           48rem;
            --left-text-margin-ratio:   0.5;
            --right-text-margin-ratio:  calc(1.0 - var(--left-text-margin-ratio));

            --gap:                      16px; /* No rem nor em since we want to keep that spacing when user changes font size at browser level */
            --scrollbar-width:          calc(100vw - 100cqw);
            --scrollbar-width-unitless: tan(atan2(var(--scrollbar-width),1px));
            --scroll-margin:            var(--gap);
            --margin-gap:               var(--gap);
            
            --grid-default-min-width:   calc(var(--line-height) + var(--gap));

        }
        
        body { /* Can only be "discovered" on the body element */
            --scrollbar-width:          calc(100vw - 100cqw);
            --scrollbar-width-unitless: tan(atan2(var(--scrollbar-width),1px));
        }

        /* To debug scrollbar width detection */
        /*body:before {
            content: counter(val) "px";
            counter-reset: val var(--scrollbar-width-unitless);
            position: fixed; top: 0px; left: 0px;
            z-index: 999;
        }*/

        /**
         * Current "standard" hack to get viewport dimentions without unit
         */

         /*
        @property --100vw { syntax: "<length>"; initial-value: 0px; inherits: false; }
        :root { --100vw: 100vw; --unitless-viewport-width: tan(atan2(var(--100vw), 1px)); }
        */

        /**
         * Fluid font size
         */
        /*
        :root { 
           
                --fluid-font-size-min-viewport-width:  320; 
                --fluid-font-size-max-viewport-width: 1600;

                --fluid-font-size-min: 1.0rem; 
                --fluid-font-size-max: 1.5rem; 

                --fluid-font-size-viewport-ratio: clamp(0, calc((var(--unitless-viewport-width) - var(--fluid-font-size-min-viewport-width)) / (var(--fluid-font-size-max-viewport-width) - var(--fluid-font-size-min-viewport-width))), 1);
                --fluid-font-size-eased-viewport-ratio: sin(var(--fluid-font-size-viewport-ratio) * 3.14159 / 2);
                --fluid-font-size: clamp(var(--fluid-font-size-min), var(--fluid-font-size-min) + ( var(--fluid-font-size-eased-viewport-ratio) * (var(--fluid-font-size-max) - var(--fluid-font-size-min)) ), var(--fluid-font-size-max));

                --root-font-size: var(--fluid-font-size);
            }*/



        /* Sanitize ++ */

        * { 
            min-width:  0;
            min-height: 0;
        }

        html {
            height: 100%;
            height: -webkit-fill-available;
            block-size: -webkit-fill-available;
            block-size: stretch;
            margin: 0px;
            padding: 0px;
            }
        body {
            min-height: 100%;
            min-height: -webkit-fill-available;
            min-block-size: -webkit-fill-available;
            min-block-size: stretch;
            margin: 0px;
            padding: 0px;
            /* Needed if we want this snippet to work with, say, a h1 element with top margin at the beginning of the body */
            position: absolute;
            top: 0;
            width: 100%;
            }

        main, header, footer, article, aside, blockquote, nav, section, details, figcaption, figure, hgroup {
            display: flow-root;
        }
        /*abbr, b, bdi, bdo, br, cite, code, data, del, dfn, em, i, ins,
        kbd, mark, meter, progress, q, s, samp, small, span, strong, 
        sub, sup, time, u, var, wbr { display: inline-block; }*/

        /* TODO Do not set margins */
        table, fieldset { margin-block: var(--gap) }

        /* Navigation */
            
        :where(nav, [role="navigation"]) li:before {
            content: "\200B";
            position: absolute;
            }
    
        :where(nav, [role="navigation"]) ul, [role="navigation"] {
            list-style: none;
            padding-inline-start: 0; /* Remove that arbitrary 40px padding, especialy within nav, where we already removed list item style */
            }
        [role="navigation"] ul[role="menu"], nav ul,
        [role="navigation"] { display: flex; gap: var(--gap); flex-wrap: wrap; } /* BEWARE: Do not break default flow. Do not make it nowrap */

        /* Inputs */

        input, button {
            font-size: inherit;
        }

        :is(input, button):not(.transparent) {
            padding: 0.25em 0.5rem;
        }
            
        /* Tables */
        
        table {
            border-collapse: collapse;
            /*width: 100%;*/ /* 100% would overflow when margins/paddings */
        }
        th, td {
            padding: 0.25rem;
            text-align: left;
            border: 1px solid currentColor;
        }

        /* Editable styles */
        
        style[contenteditable="true"] {
            display: inline-block;
            width: 100%;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: min(2vw, 0.7em);
            border-width: 1px;
            border-style: dotted;
            padding: var(--gap);
        }

        /* Typography */
        
        html                    { hanging-punctuation: first allow-end last; font-size: var(--root-font-size); line-height: var(--line-height); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        body                    { text-underline-offset: 0.24em; } /* .24 and not .25 to accomodate line heights of 1.25em with hidden overflow */

        body                    { word-break: break-word; text-wrap: pretty;   } /*
        <?= $grid ?> *          { word-break: normal; text-overflow: ellipsis; } */ /* TODO: WHy that ? */
    
        body,h1,h2,h3,h4,h5,h6  { font-family: <?= string_system_font_stack() ?>; } /* TODO: Aren't headlines inheriting it? */
             h1,h2,h3,h4,h5,h6  { text-wrap: balance; }

              :is(nav, [role="navigation"]) a          { text-decoration: none }
        a:not(:is(nav, [role="navigation"]) a)         { text-decoration-thickness: 0.5px }
        a:not(:is(nav, [role="navigation"]) a):hover   { text-decoration-thickness: 1.5px }

        :is(h1,h2,h3,h4,h5,h6) a { text-decoration: inherit; color: inherit; }

      /*ins, abbr, acronym      { } */
        u                       { text-decoration-style: wavy; }

        h1, p, button { text-box: trim-both cap alphabetic; }

        kbd {
            display:        inline-block;
            border:         2px solid currentColor;
            border-radius:  0.25rem;
            padding:        0.1em 0.2rem;
            font-size:      0.825em;
        }

        code:not(pre > code) {
            border:         2px solid currentColor;
            border-radius:  0.1rem;
            padding:        0.1em 0.2rem;
            line-height:    calc(var(--line-height) + 0.2em);
            width:          fit-content;
        }

        pre { 
            white-space: pre-wrap; /* Otherwise overflow everywhere */
            font-size: clamp(.5em, 3.5vw, 1em);
        }
    
        /* Layout */
        
        /* Main content inflate (makes footer sticky) */

        body        { display: flex; flex-direction: column; gap: 0; min-height: 100dvh; } 

        /* V1 */
        /*
        body > main { flex-grow: 1; }
        */
        /* V2 */
        /*
        body {
            justify-content: center;
            align-items:     center;
        }
        body > :is(header, footer, main)
        {
            width: 100%;
        }*/
        /* V3 */
        
        body {
            justify-content: center;
            align-items:     center;
        }
        :is(body > header) + :is(body > main)  {
            flex-grow: 1; 
        }
        body > :is(header, footer, main)
        {
            width: 100%;
        }



        /* Main */

        main { 
            
            width:          100%;
            text-align:     left;
            padding-top:    unset; /*
            margin-block:   var(--gap); */
        }
        /* COMMENT THAT BECAUSE IT FUCKS UP ALL HELLO WORLD EXAMPLES
        :not(body > header) + :is(body > main) {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }*/
        main > :is(header, footer, article, details, aside, blockquote, nav, section, details, figcaption, figure, hgroup) {

            margin-block: var(--gap);
        }

        /* Was bad looking */

        summary { cursor: pointer; } /*
        details { padding-block: var(--gap); } */
    
        :is(h1,h2,h3,h4,h5,h6), [id] {
            scroll-margin: var(--scroll-margin) 0 0 0;
        }

        /* Headlines */

        h1 {
            margin-inline: var(--gap);
            margin-block-start: 1.2em;
            margin-block-end: 1.0em;
            scroll-margin: 4em;
        }          
        h2, h3 {
            margin-block-start: 1.2em;
            margin-block-end: 1.0em;
            scroll-margin: 4em;
        }            
        h4, h5, h6 {
            margin-block-start: 1.0em;
            margin-block-end: 0.8em;
        }

        h2 { text-transform: uppercase; }

        summary :is(h1,h2,h3,h4,h5,h6) { display: inline-block; }
        

        /* Blockquote */

        blockquote  { border-left: 3px solid var(--border-color, currentColor); }
        aside       { border:      3px solid var(--border-color, currentColor); }

        /* Text limited width & heroes full width */

              :where(main, header, summary, nav, footer, article, details, aside, blockquote, section, details, figcaption, figure, hgroup, [role="document"], [role="banner"], [role="menubar"]) >
        *:where(:not(main, header, summary, nav, footer, article, details, aside, blockquote, section, details, figcaption, figure, hgroup, [role="document"], [role="banner"], [role="menubar"], span, a)) {

            --margin-inline: var(--gap);    
              margin-inline: var(--margin-inline);
        }

        :is(main, header, summary, footer) > * {

            --max-text-width-margin-inline: clamp(var(--gap), calc(var(--left-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--left-text-margin-ratio) * 100%)) 
                                            clamp(var(--gap), calc(var(--right-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--right-text-margin-ratio) * 100%));

            --margin-inline: var(--max-text-width-margin-inline);    
              margin-inline: var(--margin-inline);
        }

        /* Articles */

        body > :is(main, header, footer) > :is(article, details) {

            --mobile-no-margin-breakpoint: 400px;
            --margin-gap: clamp(0px, calc(100vw - var(--mobile-no-margin-breakpoint)), var(--gap));

            --max-text-width-margin-inline: clamp(var(--margin-gap), calc(var(--left-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--left-text-margin-ratio) * 100%)) 
                                            clamp(var(--margin-gap), calc(var(--right-text-margin-ratio) * calc(100% - var(--max-text-width))), calc(var(--right-text-margin-ratio) * 100%));

            --margin-inline: var(--max-text-width-margin-inline);
              margin-inline: var(--margin-inline);
        }

        body > :is(main, header, footer) > :is(article, details) > :is(article, details) {

            margin-inline: var(--margin-gap);
            margin-block: var(--gap);
        }

        body 
            > :is(main, header, footer) 
            > :is(article, details) 
            > :is(<?= $grid ?>, .flex) {

            margin-inline: var(--margin-gap);
            padding-block: var(--gap);
        }

        /* Others */

        :is(main, header, summary, footer, article, details, section, figure) > :is(img, figure, picture, svg, video, canvas, audio, iframe, embed, object) { 
          
            --margin-inline: 0;    
              margin-inline: var(--margin-inline);
        }

        /* Cards */

        :is(.card-title, .card-media, .card-text, .card-actions) {

            overflow: hidden; /* TODO P0 WE WANT TO AVOIR hidden overflows */
        }

        .card-media > * {

            --margin-inline: 0;    
            margin-inline: var(--margin-inline);
        }
        /* Disabled until I remember why I did this */ /* 
        .card-media > iframe {

            --margin-inline: calc(0.5 * var(--gap));
            margin-inline: var(--margin-inline);
            width: calc(100% - calc(2 * var(--margin-inline)));
        }*/

        .card-title h1 {      

            --margin-inline: .5rem;

            margin-inline:      var(--margin-inline);
            margin-block-start: var(--margin-inline);
            margin-block-end:   var(--margin-inline);
        }
          
        /* Images */

        video, iframe, img, picture, figure, canvas {
              width: 100%;
              height: auto;
              vertical-align: middle;
              display: inline-block;
            }
            
        video, iframe, img {
            max-width: 100%;
            aspect-ratio: calc(var(--width, 16) / var(--height, 10));
            object-fit: cover; 
            }

        :is(video, iframe, img).loading { object-fit: none; }

        figure { margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;  }

        img[src*=".jpg"], picture, iframe {
            background-image:       url(<?= path("img/loading.svg") ?>);
            background-repeat:      no-repeat;
            background-position:    center;
        }

        /* Figures */

        figcaption { text-align: center; }

        /* UTILITY CLASSES */

        /* EXPERIMENTAL / WIP / LINK ICONS ALL THE THINGS ! */
        a > img.link-icon:not(.no-link-icons a > img) {
            
            display: inline !important; 
            width:  16px !important; 
            height: 16px !important; 
            margin-inline-end: .25em !important;
            vertical-align: middle !important;
        }
        .no-link-icons a > img.link-icon {
            
            display: none !important;
        }

        /* Should it be part of this base (dom framework independant) css ? */
    
        :is(a, button.link):not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="//"]:after, 
        :is(a, button.link):not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="http"]:after, 
        :is(a, button.link):not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe)).external:after {

            display: inline-block;
            content: '';

            background-color: currentColor;
            mask: url('data:image/svg+xml;utf8,<svg height="1024" width="768" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M640 768H128V257.90599999999995L256 258V128H0v768h768V576H640V768zM384 128l128 128L320 448l128 128 192-192 128 128V128H384z"/></svg>');
            mask-size: cover;
                            
            position: relative;
            width:  .60em;
            height: .75em;
            top:    0.0em;
            left:   0.2em;

            margin-right: 0.33em;
            
            opacity: .4;
        }    
        a:not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="//"]:hover:after, 
        a:not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="http"]:hover:after, 
        a:not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe)).external:hover:after {

            opacity: 1.0;
        }

        @media print {
                    
            a:not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="//"]:after, 
            a:not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="http"]:after, 
            a:not([data-favicon="start"]):not(:has(img:not(.link-icon),picture,video,audio,svg,iframe)).external:after {

                content: attr(href);
            }
        }


        :is(a, button.link)[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="//"]:before, 
        :is(a, button.link)[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="http"]:before, 
        :is(a, button.link)[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe)).external:before {

            display: inline-block;
            content: '';

            background-color: currentColor;
            mask: url('data:image/svg+xml;utf8,<svg height="1024" width="768" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M640 768H128V257.90599999999995L256 258V128H0v768h768V576H640V768zM384 128l128 128L320 448l128 128 192-192 128 128V128H384z"/></svg>');
            mask-size: cover;
                            
            position: relative;
            width:  .60em;
            height: .75em;
            top:    0.0em;
            left:   0.2em;

            margin-right: 0.5em;
            
            opacity: .4;
        }    
        a[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="//"]:hover:before, 
        a[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="http"]:hover:before, 
        a[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe)).external:hover:before {

            opacity: 1.0;
        }

        @media print {
                    
            a[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="//"]:before, 
            a[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe))[href^="http"]:before, 
            a[data-favicon="start"]:not(:has(img:not(.link-icon),picture,video,audio,svg,iframe)).external:before {

                content: attr(href);
            }
        }



        /* Service worker install "call to action" */
        
        .app-install, .app-install.hidden   { display: none }
        .app-install.visible                { display: inline-block }

        /* Grid & Flex */

        <?= $grid ?> {

            --grid-default-min-width: min(300px, calc(100% - 2 * var(--gap)));

            display:                grid;
            grid-gap:               var(--gap);
            grid-template-columns:  repeat(auto-fit, minmax(var(--grid-default-min-width), 1fr));
            
            /*overflow: hidden;*/ /* if overflow is hidden, then needs to have a padding equivalent to elements box shadow size */
        }

        .flex {

            display: flex;
            flex-wrap: wrap;
            gap: var(--gap);
            align-items: center;
        }

        /* Icons */

        .icon {

            display: inline-block;
            height: auto;
            vertical-align: middle;
        }    
        .icon svg {

            width: var(--line-height);
        }

        /* Back to to button. TODO: remove? */

        .back-to-top {
            position: fixed;
            bottom: var(--gap);
            right: var(--gap);
        }

        /* Misc. */

        .hidden {
            display: none;
        }

        /* Print styles */

        @media print {
                
            :root {
    
                --max-text-width:   100%;
                --scrollbar-width:   0px;            
                --scroll-margin:     0px;
            }
        }

    <?php heredoc_flush("raw_css"); ?></style><?php return css_layer($layer, heredoc_stop(null));
}
