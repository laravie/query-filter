<?php

namespace Laravie\QueryFilter\Tests\Feature\Filters;

use Illuminate\Support\Facades\DB;
use Laravie\QueryFilter\Filters\MorphRelationSearch;
use Laravie\QueryFilter\Filters\RelationSearch;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\Factories\NoteFactory;
use Laravie\QueryFilter\Tests\Factories\PostFactory;
use Laravie\QueryFilter\Tests\Models\Note;
use Laravie\QueryFilter\Tests\Models\Post;
use Laravie\QueryFilter\Tests\Models\Video;
use Laravie\QueryFilter\Tests\TestCase;

class MorphRelationSearchTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        $posts = PostFactory::new()->times(3)->create([
            'title' => 'hello world',
        ]);

        NoteFactory::new()->create([
            'notable_type'=> Post::class,
            'notable_id' => $posts[0]->getKey(),
        ]);

        PostFactory::new()->times(5)->create([
            'title' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', [new MorphRelationSearch('notable', 'title')]
        );

        $query = Note::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "notes" where ((("notes"."notable_type" = ? and exists (select * from "posts" where "notes"."notable_id" = "posts"."id" and ("posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ?)))))',
            $query->toSql()
        );

        $this->assertSame(
            [Post::class, 'hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(1, $query->count());
    }

    /** @test */
    public function it_can_build_search_query_with_types()
    {
        $posts = PostFactory::new()->times(3)->create([
            'title' => 'hello world',
        ]);

        NoteFactory::new()->create([
            'notable_type'=> Post::class,
            'notable_id' => $posts[0]->getKey(),
        ]);

        PostFactory::new()->times(5)->create([
            'title' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', [new MorphRelationSearch('notable', 'title', [Post::class, Video::class])]
        );

        $query = Note::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "notes" where ((("notes"."notable_type" = ? and exists (select * from "posts" where "notes"."notable_id" = "posts"."id" and ("posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ?))) or ("notes"."notable_type" = ? and exists (select * from "videos" where "notes"."notable_id" = "videos"."id" and ("videos"."title" like ? or "videos"."title" like ? or "videos"."title" like ? or "videos"."title" like ?)))))',
            $query->toSql()
        );

        $this->assertSame(
            [Post::class, 'hello', 'hello%', '%hello', '%hello%', Video::class, 'hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(1, $query->count());
    }

    /** @test */
    public function it_can_build_inverse_search_query()
    {
        $posts = PostFactory::new()->times(3)->create([
            'title' => 'hello world',
        ]);

        NoteFactory::new()->create([
            'notable_type'=> Post::class,
            'notable_id' => $posts[0]->getKey(),
            'title' => 'laravel',
        ]);

        $stub = new Searchable(
            'laravel', [new RelationSearch('notes', 'title')]
        );

        $query = Post::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "posts" where (exists (select * from "notes" where "posts"."id" = "notes"."notable_id" and "notes"."notable_type" = ? and ("notes"."title" like ? or "notes"."title" like ? or "notes"."title" like ? or "notes"."title" like ?)))',
            $query->toSql()
        );

        $this->assertSame(
            [Post::class, 'laravel', 'laravel%', '%laravel', '%laravel%'],
            $query->getBindings()
        );

        $this->assertSame(1, $query->count());
    }

    /** @test */
    public function it_cannot_build_search_query_using_fluent_query_builder()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unable to use MorphRelationSearch when $query is not an instance of Illuminate\Database\Eloquent\Builder');

        $stub = new Searchable(
            'hello', [new MorphRelationSearch('posts', 'title')]
        );

        $query = DB::table('notes');
        $stub->apply($query);
    }
}
