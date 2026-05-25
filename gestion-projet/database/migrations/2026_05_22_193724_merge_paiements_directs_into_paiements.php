<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->string('type_paiement', 8)->default('indirect');
            $table->foreignId('id_convention')->nullable()->constrained('conventions', 'id_convention')->onDelete('cascade');
            $table->foreignId('id_rubrique')->nullable()->constrained('rubriques', 'id_rubrique')->onDelete('set null');
            $table->string('paiement_objet')->nullable();
            $table->text('paiement_description')->nullable();
            $table->foreignId('id_projet')->nullable()->constrained('projets', 'id_projet')->onDelete('cascade');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('id_demande')->nullable()->change();
            $table->string('paiement_mode')->nullable()->change();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE paiements ALTER COLUMN type_paiement DROP DEFAULT');
            DB::statement('ALTER TABLE paiements ALTER COLUMN type_paiement TYPE type_paiement USING type_paiement::text::type_paiement');
            DB::statement("ALTER TABLE paiements ALTER COLUMN type_paiement SET DEFAULT 'indirect'");
        }

        DB::statement("UPDATE paiements SET type_paiement = 'indirect' WHERE type_paiement IS NULL");

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('UPDATE paiements p SET id_projet = co.id_projet FROM conventions co JOIN demandes_depense dd ON dd.id_convention = co.id_convention WHERE dd.id_demande = p.id_demande');

            DB::statement('
                INSERT INTO paiements (type_paiement, id_convention, id_rubrique, paiement_montant, paiement_objet, paiement_description, paiement_date, id_enregistreur_paiement, id_projet, cree_le, mis_a_jour_le)
                SELECT
                    \'direct\'::type_paiement,
                    pd.id_convention,
                    pd.id_rubrique,
                    pd.paiement_direct_montant,
                    pd.paiement_direct_objet,
                    pd.paiement_direct_description,
                    pd.paiement_direct_date,
                    pd.id_enregistreur_paiement_direct,
                    co.id_projet,
                    pd.cree_le,
                    pd.mis_a_jour_le
                FROM paiements_directs pd
                JOIN conventions co ON co.id_convention = pd.id_convention
            ');
        }

        Schema::dropIfExists('paiements_directs');
    }

    public function down(): void
    {
        Schema::create('paiements_directs', function (Blueprint $table) {
            $table->id('id_paiement_direct');
            $table->foreignId('id_convention')->constrained('conventions', 'id_convention')->onDelete('cascade');
            $table->foreignId('id_rubrique')->nullable()->constrained('rubriques', 'id_rubrique')->onDelete('set null');
            $table->integer('paiement_direct_montant');
            $table->string('paiement_direct_objet');
            $table->text('paiement_direct_description')->nullable();
            $table->date('paiement_direct_date');
            $table->foreignId('id_enregistreur_paiement_direct')->constrained('utilisateurs', 'id_utilisateur')->onDelete('cascade');
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO paiements_directs (id_convention, id_rubrique, paiement_direct_montant, paiement_direct_objet, paiement_direct_description, paiement_direct_date, id_enregistreur_paiement_direct, cree_le, mis_a_jour_le)
            SELECT id_convention, id_rubrique, paiement_montant, paiement_objet, paiement_description, paiement_date, id_enregistreur_paiement, cree_le, mis_a_jour_le
            FROM paiements
            WHERE type_paiement = \'direct\'
        ');

        DB::statement("DELETE FROM paiements WHERE type_paiement = 'direct'");

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['id_convention']);
            $table->dropForeign(['id_rubrique']);
            $table->dropForeign(['id_projet']);
            $table->dropColumn(['type_paiement', 'id_convention', 'id_rubrique', 'paiement_objet', 'paiement_description', 'id_projet']);
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('id_demande')->nullable(false)->change();
        });
    }
};
