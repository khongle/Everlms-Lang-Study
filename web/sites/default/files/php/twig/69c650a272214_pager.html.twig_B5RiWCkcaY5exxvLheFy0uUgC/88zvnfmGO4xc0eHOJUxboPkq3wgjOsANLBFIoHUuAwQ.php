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

/* themes/custom/lms_classic/templates/navigation/pager.html.twig */
class __TwigTemplate_5d34439b66f1facf4c962fbb8c72a819 extends Template
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
        // line 7
        if ((($tmp = ($context["items"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 8
            yield "  <nav class=\"lms-pagination\" aria-labelledby=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["heading_id"] ?? null), "html", null, true);
            yield "\">
    <h4 id=\"";
            // line 9
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["heading_id"] ?? null), "html", null, true);
            yield "\" class=\"visually-hidden\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Pagination"));
            yield "</h4>

    ";
            // line 11
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "first", [], "any", false, false, true, 11)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 12
                yield "      <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "first", [], "any", false, false, true, 12), "href", [], "any", false, false, true, 12), "html", null, true);
                yield "\" title=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Go to first page"));
                yield "\" class=\"lms-pagination__item\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "first", [], "any", false, false, true, 12), "attributes", [], "any", false, false, true, 12), "href", "title"), "html", null, true);
                yield ">
        <span class=\"visually-hidden\">";
                // line 13
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("First page"));
                yield "</span>
        <span aria-hidden=\"true\">&laquo;&laquo;</span>
      </a>
    ";
            }
            // line 17
            yield "
    ";
            // line 18
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "previous", [], "any", false, false, true, 18)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 19
                yield "      <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "previous", [], "any", false, false, true, 19), "href", [], "any", false, false, true, 19), "html", null, true);
                yield "\" title=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Go to previous page"));
                yield "\" rel=\"prev\" class=\"lms-pagination__item\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "previous", [], "any", false, false, true, 19), "attributes", [], "any", false, false, true, 19), "href", "title", "rel"), "html", null, true);
                yield ">
        <span class=\"visually-hidden\">";
                // line 20
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Previous page"));
                yield "</span>
        <span aria-hidden=\"true\">&laquo;</span>
      </a>
    ";
            }
            // line 24
            yield "
    ";
            // line 25
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["ellipses"] ?? null), "previous", [], "any", false, false, true, 25)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 26
                yield "      <span class=\"lms-pagination__item lms-pagination__item--disabled\" role=\"presentation\">&hellip;</span>
    ";
            }
            // line 28
            yield "
    ";
            // line 29
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "pages", [], "any", false, false, true, 29));
            foreach ($context['_seq'] as $context["key"] => $context["item"]) {
                // line 30
                yield "      ";
                if ((($context["current"] ?? null) == $context["key"])) {
                    // line 31
                    yield "        <span class=\"lms-pagination__item lms-pagination__item--active\">
          <span class=\"visually-hidden\">
            ";
                    // line 33
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Current page"));
                    yield "
          </span>";
                    // line 34
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["key"], "html", null, true);
                    yield "
        </span>
      ";
                } else {
                    // line 37
                    yield "        <a href=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "href", [], "any", false, false, true, 37), "html", null, true);
                    yield "\" title=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Go to page @key", ["@key" => $context["key"]]));
                    yield "\" class=\"lms-pagination__item\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "attributes", [], "any", false, false, true, 37), "href", "title"), "html", null, true);
                    yield ">
          <span class=\"visually-hidden\">
            ";
                    // line 39
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Page"));
                    yield "
          </span>";
                    // line 40
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["key"], "html", null, true);
                    yield "
        </a>
      ";
                }
                // line 43
                yield "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['key'], $context['item'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 44
            yield "
    ";
            // line 45
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["ellipses"] ?? null), "next", [], "any", false, false, true, 45)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 46
                yield "      <span class=\"lms-pagination__item lms-pagination__item--disabled\" role=\"presentation\">&hellip;</span>
    ";
            }
            // line 48
            yield "
    ";
            // line 49
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "next", [], "any", false, false, true, 49)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 50
                yield "      <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "next", [], "any", false, false, true, 50), "href", [], "any", false, false, true, 50), "html", null, true);
                yield "\" title=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Go to next page"));
                yield "\" rel=\"next\" class=\"lms-pagination__item\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "next", [], "any", false, false, true, 50), "attributes", [], "any", false, false, true, 50), "href", "title", "rel"), "html", null, true);
                yield ">
        <span class=\"visually-hidden\">";
                // line 51
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Next page"));
                yield "</span>
        <span aria-hidden=\"true\">&raquo;</span>
      </a>
    ";
            }
            // line 55
            yield "
    ";
            // line 56
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "last", [], "any", false, false, true, 56)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 57
                yield "      <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "last", [], "any", false, false, true, 57), "href", [], "any", false, false, true, 57), "html", null, true);
                yield "\" title=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Go to last page"));
                yield "\" class=\"lms-pagination__item\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["items"] ?? null), "last", [], "any", false, false, true, 57), "attributes", [], "any", false, false, true, 57), "href", "title"), "html", null, true);
                yield ">
        <span class=\"visually-hidden\">";
                // line 58
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Last page"));
                yield "</span>
        <span aria-hidden=\"true\">&raquo;&raquo;</span>
      </a>
    ";
            }
            // line 62
            yield "  </nav>
";
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["items", "heading_id", "ellipses", "current"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/lms_classic/templates/navigation/pager.html.twig";
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
        return array (  207 => 62,  200 => 58,  191 => 57,  189 => 56,  186 => 55,  179 => 51,  170 => 50,  168 => 49,  165 => 48,  161 => 46,  159 => 45,  156 => 44,  150 => 43,  144 => 40,  140 => 39,  130 => 37,  124 => 34,  120 => 33,  116 => 31,  113 => 30,  109 => 29,  106 => 28,  102 => 26,  100 => 25,  97 => 24,  90 => 20,  81 => 19,  79 => 18,  76 => 17,  69 => 13,  60 => 12,  58 => 11,  51 => 9,  46 => 8,  44 => 7,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/lms_classic/templates/navigation/pager.html.twig", "/var/www/html/web/themes/custom/lms_classic/templates/navigation/pager.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 7, "for" => 29];
        static $filters = ["escape" => 8, "t" => 9, "without" => 12];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape', 't', 'without'],
                [],
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
