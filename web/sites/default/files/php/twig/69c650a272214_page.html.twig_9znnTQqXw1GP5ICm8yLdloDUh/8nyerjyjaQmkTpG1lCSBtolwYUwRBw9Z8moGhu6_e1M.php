<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* themes/custom/lms_classic/templates/page/page.html.twig */
class __TwigTemplate_c752a5eaf801580d27ec7ba8d7853077 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 2
        yield "<div class=\"lms-page\">
  ";
        // line 4
        yield "  <header class=\"lms-navbar\">
    <div class=\"lms-navbar__container\">
      <a href=\"";
        // line 6
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("<front>"));
        yield "\" class=\"lms-navbar__brand\">
        <i class=\"fas fa-graduation-cap\"></i> ";
        // line 7
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("EverLMS"));
        yield "
      </a>

      <button class=\"lms-navbar__toggle\" aria-label=\"Toggle navigation\">
        <i class=\"fas fa-bars\"></i>
      </button>

      <nav class=\"lms-navbar__nav\" role=\"navigation\">
        <a href=\"";
        // line 15
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("<front>"));
        yield "\" class=\"lms-navbar__link ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((((($context["current_route"] ?? null) == "lms_course.homepage") || (($context["current_route"] ?? null) == "<front>"))) ? ("lms-navbar__link--active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Home"));
        yield "</a>
        <a href=\"";
        // line 16
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.catalog"));
        yield "\" class=\"lms-navbar__link ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((CoreExtension::inFilter(($context["current_route"] ?? null), ["lms_course.catalog", "lms_course.detail"])) ? ("lms-navbar__link--active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Courses"));
        yield "</a>
        <a href=\"";
        // line 17
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.category"));
        yield "\" class=\"lms-navbar__link ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((CoreExtension::inFilter(($context["current_route"] ?? null), ["lms_course.category", "lms_course.category_detail"])) ? ("lms-navbar__link--active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Categories"));
        yield "</a>
        <a href=\"";
        // line 18
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_forum.index"));
        yield "\" class=\"lms-navbar__link ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((CoreExtension::inFilter(($context["current_route"] ?? null), ["lms_forum.index", "entity.lms_forum_topic.canonical"])) ? ("lms-navbar__link--active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Community"));
        yield "</a>
        <a href=\"";
        // line 19
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.pricing"));
        yield "\" class=\"lms-navbar__link ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_route"] ?? null) == "lms_course.pricing")) ? ("lms-navbar__link--active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Pricing"));
        yield "</a>
        <a href=\"";
        // line 20
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_blog.frontend.collection"));
        yield "\" class=\"lms-navbar__link ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((((($context["current_route"] ?? null) == "lms_blog.frontend.collection") || (($context["current_route"] ?? null) == "lms_blog.frontend.canonical"))) ? ("lms-navbar__link--active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("News"));
        yield "</a>
        ";
        // line 22
        yield "        ";
        // line 23
        yield "      </nav>

      <div class=\"lms-navbar__actions\">
        ";
        // line 26
        if ((($tmp = ($context["logged_in"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 27
            yield "          <div style=\"display: flex; align-items: center; gap: 1rem;\">
            ";
            // line 28
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["notification_bell"] ?? null), "html", null, true);
            yield "
            
            <a href=\"";
            // line 30
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_wishlist.my_wishlist"));
            yield "\" class=\"lms-navbar__link\" style=\"position: relative; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; margin-right: 0.5rem; transition: all 0.2s ease;\" title=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("My Wishlist"));
            yield "\" onmouseover=\"this.style.background='rgba(255,255,255,0.05)';\" onmouseout=\"this.style.background='transparent';\">
              <i class=\"far fa-heart\" id=\"lms-wishlist-icon\" style=\"font-size: 1.15rem; color: #8b949e; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);\"></i>
              <span id=\"lms-wishlist-counter\" style=\"position: absolute; top: -3px; right: -4px; background: #f5576c; color: white; border-radius: 50%; font-size: 0.65rem; font-weight: bold; min-width: 16px; height: 16px; display: none; align-items: center; justify-content: center; border: 2px solid #0d1117; box-sizing: content-box; z-index: 2;\">0</span>
            </a>
            
            <a href=\"";
            // line 35
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("user.page"));
            yield "\" style=\"display: flex; align-items: center; gap: 0.5rem; text-decoration: none;\" class=\"lms-user-profile-link\">
              ";
            // line 36
            if ((($tmp = ($context["current_user_picture"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 37
                yield "                <img src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["current_user_picture"] ?? null), "html", null, true);
                yield "\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["current_user_name"] ?? null), "html", null, true);
                yield "\" style=\"width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);\">
              ";
            } else {
                // line 39
                yield "                <div style=\"width: 32px; height: 32px; border-radius: 50%; background: #21262d; display: flex; align-items: center; justify-content: center; color: #8b949e; font-size: 14px; border: 1px solid hsla(0,0%,100%,.1);\">
                  <i class=\"fas fa-user\"></i>
                </div>
              ";
            }
            // line 43
            yield "              <span style=\"color: #e6edf3; font-weight: 600; font-size: 0.9375rem;\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["current_user_name"] ?? null), "html", null, true);
            yield "</span>
            </a>
            
            ";
            // line 46
            if ((($tmp = ($context["is_manager"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 47
                yield "              <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.admin_dashboard"));
                yield "\" class=\"lms-btn lms-btn--sm lms-btn--outline\"><i class=\"fas fa-briefcase\"></i> ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Workspace"));
                yield "</a>
            ";
            } else {
                // line 49
                yield "              <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_progress.dashboard"));
                yield "\" class=\"lms-btn lms-btn--sm lms-btn--outline\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Dashboard"));
                yield "</a>
            ";
            }
            // line 51
            yield "            <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("user.logout"));
            yield "\" class=\"lms-navbar__link\" style=\"margin-left: 0.5rem;\" title=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Logout"));
            yield "\">
              <i class=\"fas fa-sign-out-alt\"></i>
            </a>
          </div>
        ";
        } else {
            // line 56
            yield "          <a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("user.login"));
            yield "\" class=\"lms-navbar__link\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Login"));
            yield "</a>
          <a href=\"";
            // line 57
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("user.register"));
            yield "\" class=\"lms-btn lms-btn--sm lms-btn--primary\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Register"));
            yield "</a>
        ";
        }
        // line 59
        yield "      </div>
    </div>
  </header>



  ";
        // line 66
        yield "  <main id=\"main-content\" class=\"lms-page__content\">
    ";
        // line 67
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "highlighted", [], "any", false, false, true, 67)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 68
            yield "      <div class=\"lms-messages-wrapper\" style=\"margin-bottom: 2rem;\">
        ";
            // line 69
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "highlighted", [], "any", false, false, true, 69), "html", null, true);
            yield "
      </div>
    ";
        }
        // line 72
        yield "    ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 72), "html", null, true);
        yield "
  </main>

  ";
        // line 76
        yield "  <footer class=\"lms-footer\">
    <div class=\"lms-footer__container\">
      <div class=\"lms-footer__grid\">
        <div class=\"lms-footer__col\">
          <h4>";
        // line 80
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Quick Links"));
        yield "</h4>
          <ul>
            <li><a href=\"";
        // line 82
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("<front>"));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Home"));
        yield "</a></li>
            <li><a href=\"";
        // line 83
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.catalog"));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Courses"));
        yield "</a></li>
            <li><a href=\"";
        // line 84
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.category"));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Categories"));
        yield "</a></li>
            <li><a href=\"";
        // line 85
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_forum.index"));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Community"));
        yield "</a></li>
            <li><a href=\"#\">";
        // line 86
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Blog"));
        yield "</a></li>
          </ul>
        </div>
        <div class=\"lms-footer__col\">
          <h4>";
        // line 90
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Course"));
        yield "</h4>
          <ul>
            <li><a href=\"#\">";
        // line 92
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("News & Notices"));
        yield "</a></li>
            <li><a href=\"";
        // line 93
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.pricing"));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Pricing"));
        yield "</a></li>
            <li><a href=\"";
        // line 94
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_blog.frontend.collection"));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("News"));
        yield "</a></li>
            <li><a href=\"#\">";
        // line 95
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Course Guides"));
        yield "</a></li>
          </ul>
        </div>
        <div class=\"lms-footer__col\">
          <h4>";
        // line 99
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Supports"));
        yield "</h4>
          <ul>
            <li><a href=\"#\">";
        // line 101
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("About Us"));
        yield "</a></li>
            <li><a href=\"#\">";
        // line 102
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Supports"));
        yield "</a></li>
            <li><a href=\"#\">";
        // line 103
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("FAQ"));
        yield "</a></li>
            <li><a href=\"#\">";
        // line 104
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Contact Us"));
        yield "</a></li>
          </ul>
        </div>
        <div class=\"lms-footer__col\">
          <h4>";
        // line 108
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Contact Info"));
        yield "</h4>
          <ul>
            <li><i class=\"fas fa-map-marker-alt\"></i> ";
        // line 110
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Company Headquarters, District"));
        yield "</li>
            <li><i class=\"fas fa-phone\"></i> +01 00 200 0000</li>
            <li><i class=\"fas fa-phone\"></i> +01 00 200 0000</li>
            <li><i class=\"fas fa-envelope\"></i> everlms@gmail.com</li>
          </ul>
        </div>
      </div>

      <div class=\"lms-footer__bottom\">
        <p>";
        // line 119
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Powered by"));
        yield " <a href=\"#\">EverLMS</a></p>
      </div>
    </div>
  </footer>
</div>

";
        // line 125
        if ((($tmp = ($context["logged_in"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 126
            yield "<script>
document.addEventListener('DOMContentLoaded', () => {
  const wishlistToggleBtns = document.querySelectorAll('.lms-wishlist-toggle');
  const courseIds = Array.from(wishlistToggleBtns).map(btn => btn.dataset.courseId).filter(Boolean);
  const counterBatch = document.getElementById('lms-wishlist-counter');
  
  if (courseIds.length > 0 || counterBatch) {
    const idsUnique = [...new Set(courseIds)].join(',');
    fetch('/api/lms/wishlist/status?course_ids=' + idsUnique)
      .then(r => r.json())
      .then(data => {
        // Update counter & visual fill
        if (data.count !== undefined) {
          if (counterBatch) {
            counterBatch.textContent = data.count;
            counterBatch.style.display = data.count > 0 ? 'flex' : 'none';
          }
          const mainIcon = document.getElementById('lms-wishlist-icon');
          if (mainIcon) {
            if (data.count > 0) {
              mainIcon.classList.remove('far');
              mainIcon.classList.add('fas');
              mainIcon.style.color = '#f5576c';
            } else {
              mainIcon.classList.remove('fas');
              mainIcon.classList.add('far');
              mainIcon.style.color = '#8b949e';
            }
          }
        }
        
        // Update cards
        if (data.status) {
          wishlistToggleBtns.forEach(btn => {
            const cid = btn.dataset.courseId;
            const icon = btn.querySelector('i');
            const textSpan = btn.querySelector('.wishlist-text');
            if (data.status[cid] === true) {
              icon.classList.remove('far');
              icon.classList.add('fas');
              icon.style.color = '#ff7b72';
              if (textSpan) textSpan.textContent = 'Remove from Wishlist';
              btn.style.borderColor = 'rgba(255,123,114,0.3)';
            }
          });
        }
      });
  }

  wishlistToggleBtns.forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      const cid = btn.dataset.courseId;
      const icon = btn.querySelector('i');
      const textSpan = btn.querySelector('.wishlist-text');
      
      // Optimistic UI update
      const isCurrentlyWishlisted = icon.classList.contains('fas');
      if (isCurrentlyWishlisted) {
        icon.classList.remove('fas');
        icon.classList.add('far');
        icon.style.color = '';
        if (textSpan) textSpan.textContent = 'Add to Wishlist';
        btn.style.borderColor = '';
        if (counterBatch) {
          let c = parseInt(counterBatch.textContent) || 0;
          c = Math.max(0, c - 1);
          counterBatch.textContent = c;
          counterBatch.style.display = c > 0 ? 'flex' : 'none';
          const mainIcon = document.getElementById('lms-wishlist-icon');
          if (mainIcon) {
            if (c > 0) {
              mainIcon.classList.remove('far'); mainIcon.classList.add('fas'); mainIcon.style.color = '#f5576c';
            } else {
              mainIcon.classList.remove('fas'); mainIcon.classList.add('far'); mainIcon.style.color = '#8b949e';
            }
          }
        }
      } else {
        icon.classList.remove('far');
        icon.classList.add('fas');
        icon.style.color = '#ff7b72';
        if (textSpan) textSpan.textContent = 'Remove from Wishlist';
        btn.style.borderColor = 'rgba(255,123,114,0.3)';
        if (counterBatch) {
          let c = parseInt(counterBatch.textContent) || 0;
          c += 1;
          counterBatch.textContent = c;
          counterBatch.style.display = c > 0 ? 'flex' : 'none';
          const mainIcon = document.getElementById('lms-wishlist-icon');
          if (mainIcon) {
            mainIcon.classList.remove('far'); mainIcon.classList.add('fas'); mainIcon.style.color = '#f5576c';
          }
        }
      }
      
      // Animate heartbeat effect
      icon.style.transform = 'scale(1.3)';
      setTimeout(() => { icon.style.transform = 'scale(1)'; }, 200);
      
      try {
        await fetch('/api/lms/wishlist/toggle', {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
          body: JSON.stringify({course_id: cid})
        });
      } catch (err) {
        console.error('Failed to toggle wishlist', err);
      }
    });
  });
});
</script>
<style>
.lms-wishlist-toggle i {
  transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.2s ease;
}
.lms-wishlist-toggle:hover i {
  color: #ff7b72;
}
</style>
";
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["current_route", "logged_in", "notification_bell", "current_user_picture", "current_user_name", "is_manager", "page"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/lms_classic/templates/page/page.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  362 => 126,  360 => 125,  351 => 119,  339 => 110,  334 => 108,  327 => 104,  323 => 103,  319 => 102,  315 => 101,  310 => 99,  303 => 95,  297 => 94,  291 => 93,  287 => 92,  282 => 90,  275 => 86,  269 => 85,  263 => 84,  257 => 83,  251 => 82,  246 => 80,  240 => 76,  233 => 72,  227 => 69,  224 => 68,  222 => 67,  219 => 66,  211 => 59,  204 => 57,  197 => 56,  186 => 51,  178 => 49,  170 => 47,  168 => 46,  161 => 43,  155 => 39,  147 => 37,  145 => 36,  141 => 35,  131 => 30,  126 => 28,  123 => 27,  121 => 26,  116 => 23,  114 => 22,  106 => 20,  98 => 19,  90 => 18,  82 => 17,  74 => 16,  66 => 15,  55 => 7,  51 => 6,  47 => 4,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/lms_classic/templates/page/page.html.twig", "/var/www/html/web/themes/custom/lms_classic/templates/page/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 26];
        static $filters = ["t" => 7, "escape" => 28];
        static $functions = ["path" => 6];

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['t', 'escape'],
                ['path'],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
