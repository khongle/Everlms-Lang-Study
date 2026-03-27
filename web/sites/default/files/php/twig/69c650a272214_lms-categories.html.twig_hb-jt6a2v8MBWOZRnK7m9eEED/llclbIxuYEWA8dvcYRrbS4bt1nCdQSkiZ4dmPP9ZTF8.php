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

/* modules/custom/lms_course/templates/lms-categories.html.twig */
class __TwigTemplate_82c82f82f709f168797b53da550ac3bc extends Template
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
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("lms_theme/global"), "html", null, true);
        yield "

<section class=\"lms-section\">
  <div class=\"container\">
    <div class=\"lms-manage__header\" style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;\">
      <h1 class=\"lms-section__title\" style=\"margin: 0;\">";
        // line 7
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Course Categories"));
        yield "</h1>
      <div class=\"lms-search-box\" style=\"position: relative; width: 100%; max-width: 320px;\">
        <i class=\"fas fa-search\" style=\"position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #8b949e;\"></i>
        <input type=\"text\" id=\"category-search-input\" placeholder=\"";
        // line 10
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Search categories..."));
        yield "\" style=\"width: 100%; background: #161b22; border: 1px solid hsla(0,0%,100%,0.1); color: #e6edf3; padding: 0.6rem 1rem 0.6rem 2.5rem; border-radius: 2rem; font-size: 0.9rem; transition: all 0.3s ease; outline: none;\" onfocus=\"this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.2)'\" onblur=\"this.style.borderColor='hsla(0,0%,100%,0.1)'; this.style.boxShadow='none'\">
      </div>
    </div>

    ";
        // line 14
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["categories"] ?? null)) > 0)) {
            // line 15
            yield "      <div class=\"lms-categories-grid\">
        ";
            // line 16
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["categories"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["category"]) {
                // line 17
                yield "          ";
                $context["term"] = CoreExtension::getAttribute($this->env, $this->source, $context["category"], "entity", [], "any", false, false, true, 17);
                // line 18
                yield "          ";
                $context["count"] = CoreExtension::getAttribute($this->env, $this->source, $context["category"], "course_count", [], "any", false, false, true, 18);
                // line 19
                yield "          <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.category_detail", ["taxonomy_term" => CoreExtension::getAttribute($this->env, $this->source, ($context["term"] ?? null), "id", [], "method", false, false, true, 19)]), "html", null, true);
                yield "\" class=\"lms-category-card\">
            <div class=\"lms-category-card__icon\">
              ";
                // line 21
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["category"], "thumbnail_url", [], "any", false, false, true, 21)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 22
                    yield "                 <img src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["category"], "thumbnail_url", [], "any", false, false, true, 22), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["term"] ?? null), "label", [], "any", false, false, true, 22), "html", null, true);
                    yield "\" />
              ";
                } else {
                    // line 24
                    yield "                 <i class=\"fas fa-book\"></i>
              ";
                }
                // line 26
                yield "            </div>
            <div class=\"lms-category-card__content\">
              <h3 class=\"lms-category-card__title\">";
                // line 28
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["term"] ?? null), "label", [], "any", false, false, true, 28), "html", null, true);
                yield "</h3>
              ";
                // line 29
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["term"] ?? null), "description", [], "any", false, false, true, 29), "value", [], "any", false, false, true, 29)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 30
                    yield "                <p class=\"lms-category-card__desc\">";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags($this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["term"] ?? null), "description", [], "any", false, false, true, 30), "value", [], "any", false, false, true, 30))), "html", null, true);
                    yield "</p>
              ";
                }
                // line 32
                yield "            </div>
            <div class=\"lms-category-card__meta\">
              <span class=\"lms-badge\">";
                // line 34
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["count"] ?? null), "html", null, true);
                yield " ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["count"] ?? null) == 1)) ? (t("Course")) : (t("Courses"))));
                yield "</span>
            </div>
          </a>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['category'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 38
            yield "      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const searchInput = document.getElementById('category-search-input');
          const categoryCards = document.querySelectorAll('.lms-category-card');

          if (searchInput) {
            searchInput.addEventListener('input', function(e) {
              const term = e.target.value.toLowerCase().trim();
              
              categoryCards.forEach(card => {
                const title = card.querySelector('.lms-category-card__title').textContent.toLowerCase();
                const descEl = card.querySelector('.lms-category-card__desc');
                const desc = descEl ? descEl.textContent.toLowerCase() : '';
                
                if (title.includes(term) || desc.includes(term)) {
                  card.style.display = 'flex';
                } else {
                  card.style.display = 'none';
                }
              });
            });
          }
        });
      </script>
    ";
        } else {
            // line 65
            yield "      <div class=\"lms-empty-state\">
        <i class=\"fas fa-folder-open\"></i>
        <h3>";
            // line 67
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No categories yet"));
            yield "</h3>
      </div>
    ";
        }
        // line 70
        yield "  </div>
</section>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["categories"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_course/templates/lms-categories.html.twig";
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
        return array (  171 => 70,  165 => 67,  161 => 65,  132 => 38,  120 => 34,  116 => 32,  110 => 30,  108 => 29,  104 => 28,  100 => 26,  96 => 24,  88 => 22,  86 => 21,  80 => 19,  77 => 18,  74 => 17,  70 => 16,  67 => 15,  65 => 14,  58 => 10,  52 => 7,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_course/templates/lms-categories.html.twig", "/var/www/html/web/modules/custom/lms_course/templates/lms-categories.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 14, "for" => 16, "set" => 17];
        static $filters = ["escape" => 2, "t" => 7, "length" => 14, "striptags" => 30, "render" => 30];
        static $functions = ["attach_library" => 2, "path" => 19];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for', 'set'],
                ['escape', 't', 'length', 'striptags', 'render'],
                ['attach_library', 'path'],
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
