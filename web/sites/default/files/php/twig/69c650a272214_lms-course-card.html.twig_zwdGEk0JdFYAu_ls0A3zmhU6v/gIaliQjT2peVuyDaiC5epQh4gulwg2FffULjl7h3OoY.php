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

/* modules/custom/lms_course/templates/lms-course-card.html.twig */
class __TwigTemplate_6996f427862a9484b3e1bb99966bdf6e extends Template
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
        $context["target_link"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "is_enrolled", [], "any", false, false, true, 2) && CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "continue_url", [], "any", false, false, true, 2))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "continue_url", [], "any", false, false, true, 2)) : (CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "url", [], "any", false, false, true, 2)));
        // line 3
        yield "<div class=\"card-lms card-lms--course\" data-category=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "category_id", [], "any", false, false, true, 3), "html", null, true);
        yield "\" data-level=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "level_id", [], "any", false, false, true, 3), "html", null, true);
        yield "\" data-price=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price_raw", [], "any", false, false, true, 3), "html", null, true);
        yield "\" data-rating=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", true, true, true, 3)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", false, false, true, 3), 5.0)) : (5.0)), "html", null, true);
        yield "\">
  ";
        // line 4
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "has_thumbnail", [], "any", false, false, true, 4)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 5
            yield "    <div class=\"card-lms__image\">
      <div style=\"position: absolute; top: 0.5rem; left: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; z-index: 10; align-items: flex-start;\">
        ";
            // line 7
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "category", [], "any", false, false, true, 7)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 8
                yield "          <span class=\"card-lms__category\" style=\"position: static; margin: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.5);\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "category", [], "any", false, false, true, 8), "html", null, true);
                yield "</span>
        ";
            }
            // line 10
            yield "        ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "bestseller", [], "any", false, false, true, 10)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 11
                yield "          <span class=\"badge-lms badge-lms--warning\" style=\"box-shadow: 0 2px 4px rgba(0,0,0,0.5);\">Bestseller</span>
        ";
            }
            // line 13
            yield "      </div>
      <a href=\"";
            // line 14
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["target_link"] ?? null), "html", null, true);
            yield "\" style=\"display: block; color: inherit; text-decoration: none; height: 100%;\">
        <img src=\"";
            // line 15
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "thumbnail_url", [], "any", false, false, true, 15), "html", null, true);
            yield "\" alt=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "name", [], "any", false, false, true, 15), "html", null, true);
            yield "\" loading=\"lazy\">
      </a>
      
      <button class=\"lms-wishlist-toggle lms-btn-icon\" data-course-id=\"";
            // line 18
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "id", [], "any", false, false, true, 18), "html", null, true);
            yield "\" style=\"position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(0,0,0,0.5); color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; padding: 0;\" aria-label=\"Toggle Wishlist\">
        <i class=\"";
            // line 19
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "is_wishlisted", [], "any", false, false, true, 19)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fas") : ("far")));
            yield " fa-heart\" ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "is_wishlisted", [], "any", false, false, true, 19)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "style=\"color: #ff7b72;\"";
            }
            yield "></i>
      </button>

      ";
            // line 22
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price_raw", [], "any", false, false, true, 22) == 0)) {
                // line 23
                yield "        <span class=\"badge-lms badge-lms--success\" style=\"position: absolute; bottom: 12px; left: 12px; z-index: 10;\">Free</span>
      ";
            }
            // line 25
            yield "    </div>
  ";
        } else {
            // line 27
            yield "    <div class=\"card-lms__image card-lms__image--placeholder\">
      <div style=\"position: absolute; top: 0.5rem; left: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; z-index: 10; align-items: flex-start;\">
        ";
            // line 29
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "category", [], "any", false, false, true, 29)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 30
                yield "          <span class=\"card-lms__category\" style=\"position: static; margin: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.5);\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "category", [], "any", false, false, true, 30), "html", null, true);
                yield "</span>
        ";
            }
            // line 32
            yield "        ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "bestseller", [], "any", false, false, true, 32)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 33
                yield "          <span class=\"badge-lms badge-lms--warning\" style=\"box-shadow: 0 2px 4px rgba(0,0,0,0.5);\">Bestseller</span>
        ";
            }
            // line 35
            yield "      </div>
      <a href=\"";
            // line 36
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["target_link"] ?? null), "html", null, true);
            yield "\" style=\"display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; color: inherit; text-decoration: none;\">
        <i class=\"fas fa-graduation-cap\"></i>
      </a>
      <button class=\"lms-wishlist-toggle lms-btn-icon\" data-course-id=\"";
            // line 39
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "id", [], "any", false, false, true, 39), "html", null, true);
            yield "\" style=\"position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(0,0,0,0.5); color: white; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; padding: 0;\" aria-label=\"Toggle Wishlist\">
        <i class=\"";
            // line 40
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "is_wishlisted", [], "any", false, false, true, 40)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fas") : ("far")));
            yield " fa-heart\" ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "is_wishlisted", [], "any", false, false, true, 40)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "style=\"color: #ff7b72;\"";
            }
            yield "></i>
      </button>
      ";
            // line 42
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price_raw", [], "any", false, false, true, 42) == 0)) {
                // line 43
                yield "        <span class=\"badge-lms badge-lms--success\" style=\"position: absolute; bottom: 12px; left: 12px; z-index: 10;\">Free</span>
      ";
            }
            // line 45
            yield "    </div>
  ";
        }
        // line 47
        yield "
  <div class=\"card-lms__body\">
    <h3 class=\"card-lms__title\">
      <a href=\"";
        // line 50
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "url", [], "any", false, false, true, 50), "html", null, true);
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "name", [], "any", false, false, true, 50), "html", null, true);
        yield "</a>
    </h3>

    ";
        // line 53
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_name", [], "any", false, false, true, 53)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "      <div class=\"card-lms__instructor\" style=\"display: flex; align-items: center; gap: 8px; margin-bottom: 8px;\">
        ";
            // line 55
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_avatar", [], "any", false, false, true, 55)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 56
                yield "          <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_url", [], "any", true, true, true, 56)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_url", [], "any", false, false, true, 56), "#")) : ("#")), "html", null, true);
                yield "\"><img src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_avatar", [], "any", false, false, true, 56), "html", null, true);
                yield "\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_name", [], "any", false, false, true, 56), "html", null, true);
                yield "\" class=\"card-lms__instructor-avatar\" style=\"width: 24px; height: 24px; border-radius: 50%; object-fit: cover;\"></a>
        ";
            } else {
                // line 58
                yield "          <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_url", [], "any", true, true, true, 58)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_url", [], "any", false, false, true, 58), "#")) : ("#")), "html", null, true);
                yield "\"><div class=\"card-lms__instructor-avatar-fallback\" style=\"width: 24px; height: 24px; border-radius: 50%; background: #333; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #fff;\">
            <i class=\"fas fa-user\"></i>
          </div></a>
        ";
            }
            // line 62
            yield "        <span class=\"card-lms__instructor-name\" style=\"font-size: 0.85rem; color: #a1a1aa;\"><a href=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_url", [], "any", true, true, true, 62)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_url", [], "any", false, false, true, 62), "#")) : ("#")), "html", null, true);
            yield "\" style=\"color: inherit; text-decoration: none;\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "instructor_name", [], "any", false, false, true, 62), "html", null, true);
            yield "</a></span>
      </div>
    ";
        }
        // line 65
        yield "
    ";
        // line 66
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "short_description", [], "any", false, false, true, 66)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 67
            yield "      <div class=\"card-lms__desc\">
        <a href=\"";
            // line 68
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["target_link"] ?? null), "html", null, true);
            yield "\" style=\"color: inherit; text-decoration: none; display: block;\">
          ";
            // line 69
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "short_description", [], "any", false, false, true, 69)), "html", null, true);
            yield "
        </a>
      </div>
    ";
        }
        // line 73
        yield "
    <div class=\"card-lms__meta\">
      ";
        // line 75
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "level", [], "any", false, false, true, 75)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 76
            yield "        <span class=\"card-lms__level\"><i class=\"fas fa-signal\"></i> ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "level", [], "any", false, false, true, 76), "html", null, true);
            yield "</span>
      ";
        }
        // line 78
        yield "      ";
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "duration", [], "any", false, false, true, 78)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 79
            yield "        <span class=\"card-lms__duration\"><i class=\"fas fa-clock\"></i> ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "duration", [], "any", false, false, true, 79), "html", null, true);
            yield "</span>
      ";
        }
        // line 81
        yield "      <span class=\"card-lms__rating\" style=\"margin-left: auto; color: #fee140;\">
        ";
        // line 82
        $context["rating_floor"] = Twig\Extension\CoreExtension::round(((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", true, true, true, 82)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", false, false, true, 82), 5.0)) : (5.0)), 0, "floor");
        // line 83
        yield "        ";
        $context["has_half"] = ((((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", true, true, true, 83)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", false, false, true, 83), 5.0)) : (5.0)) - ($context["rating_floor"] ?? null)) >= 0.5);
        // line 84
        yield "        ";
        $context["empty_stars"] = ((5 - ($context["rating_floor"] ?? null)) - (((($tmp = ($context["has_half"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (1) : (0)));
        // line 85
        yield "        
        ";
        // line 86
        if ((($context["rating_floor"] ?? null) > 0)) {
            // line 87
            yield "          ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(range(1, ($context["rating_floor"] ?? null)));
            foreach ($context['_seq'] as $context["_key"] => $context["i"]) {
                // line 88
                yield "            <i class=\"fas fa-star\"></i>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['i'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 90
            yield "        ";
        }
        // line 91
        yield "        
        ";
        // line 92
        if ((($tmp = ($context["has_half"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 93
            yield "          <i class=\"fas fa-star-half-alt\"></i>
        ";
        }
        // line 95
        yield "        
        ";
        // line 96
        if ((($context["empty_stars"] ?? null) > 0)) {
            // line 97
            yield "          ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(range(1, ($context["empty_stars"] ?? null)));
            foreach ($context['_seq'] as $context["_key"] => $context["i"]) {
                // line 98
                yield "            <i class=\"far fa-star\"></i>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['i'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 100
            yield "        ";
        }
        // line 101
        yield "        <span style=\"color: #a1a1aa; margin-left: 4px;\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Twig\Extension\CoreExtension']->formatNumber(((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", true, true, true, 101)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "rating", [], "any", false, false, true, 101), 5.0)) : (5.0)), 1), "html", null, true);
        yield "</span>
      </span>
    </div>

    <div class=\"card-lms__footer\" style=\"display: flex; align-items: center; justify-content: space-between;\">
      ";
        // line 106
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "is_enrolled", [], "any", false, false, true, 106)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 107
            yield "        <div style=\"display: flex; flex-direction: column; gap: 2px;\">
           <span style=\"font-size: 0.7rem; color: #34d399; font-weight: 700; text-transform: uppercase; text-shadow: 0 0 10px rgba(52, 211, 153, 0.3);\"><i class=\"fas fa-unlock\"></i> Unlocked</span>
           ";
            // line 109
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price_raw", [], "any", false, false, true, 109) > 0)) {
                // line 110
                yield "             <span class=\"card-lms__price\" style=\"font-size: 0.9rem; color: #52525b; text-decoration: line-through;\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price", [], "any", false, false, true, 110), "html", null, true);
                yield "</span>
           ";
            } else {
                // line 112
                yield "             <span class=\"card-lms__price\" style=\"font-size: 0.9rem; color: #52525b;\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price", [], "any", false, false, true, 112), "html", null, true);
                yield "</span>
           ";
            }
            // line 114
            yield "        </div>
        <a href=\"";
            // line 115
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "continue_url", [], "any", false, false, true, 115), "html", null, true);
            yield "\" class=\"btn-lms-primary btn-lms-sm\" style=\"border-radius: 2rem; box-shadow: 0 0 10px rgba(168, 85, 247, 0.2);\">
          ";
            // line 116
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Continue Learning"));
            yield "
        </a>
      ";
        } else {
            // line 119
            yield "        <span class=\"card-lms__price ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price_raw", [], "any", false, false, true, 119) == 0)) {
                yield "card-lms__price--free";
            }
            yield "\" style=\"font-size: 1.25rem;\">
          ";
            // line 120
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "price", [], "any", false, false, true, 120), "html", null, true);
            yield "
        </span>
        <a href=\"";
            // line 122
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["card"] ?? null), "url", [], "any", false, false, true, 122), "html", null, true);
            yield "\" class=\"btn-lms-outline btn-lms-sm\" style=\"border-radius: 2rem;\">
          ";
            // line 123
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Enroll Now"));
            yield "
        </a>
      ";
        }
        // line 126
        yield "    </div>
  </div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["card"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_course/templates/lms-course-card.html.twig";
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
        return array (  379 => 126,  373 => 123,  369 => 122,  364 => 120,  357 => 119,  351 => 116,  347 => 115,  344 => 114,  338 => 112,  332 => 110,  330 => 109,  326 => 107,  324 => 106,  315 => 101,  312 => 100,  305 => 98,  300 => 97,  298 => 96,  295 => 95,  291 => 93,  289 => 92,  286 => 91,  283 => 90,  276 => 88,  271 => 87,  269 => 86,  266 => 85,  263 => 84,  260 => 83,  258 => 82,  255 => 81,  249 => 79,  246 => 78,  240 => 76,  238 => 75,  234 => 73,  227 => 69,  223 => 68,  220 => 67,  218 => 66,  215 => 65,  206 => 62,  198 => 58,  188 => 56,  186 => 55,  183 => 54,  181 => 53,  173 => 50,  168 => 47,  164 => 45,  160 => 43,  158 => 42,  149 => 40,  145 => 39,  139 => 36,  136 => 35,  132 => 33,  129 => 32,  123 => 30,  121 => 29,  117 => 27,  113 => 25,  109 => 23,  107 => 22,  97 => 19,  93 => 18,  85 => 15,  81 => 14,  78 => 13,  74 => 11,  71 => 10,  65 => 8,  63 => 7,  59 => 5,  57 => 4,  46 => 3,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_course/templates/lms-course-card.html.twig", "/var/www/html/web/modules/custom/lms_course/templates/lms-course-card.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 2, "if" => 4, "for" => 87];
        static $filters = ["escape" => 3, "default" => 3, "striptags" => 69, "round" => 82, "number_format" => 101, "t" => 116];
        static $functions = ["range" => 87];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'for'],
                ['escape', 'default', 'striptags', 'round', 'number_format', 't'],
                ['range'],
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
