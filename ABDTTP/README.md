# ABDTTP

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Recommended Browser Setup

- Chromium-based browsers (Chrome, Edge, Brave, etc.):
  - [Vue.js devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd)
  - [Turn on Custom Object Formatter in Chrome DevTools](http://bit.ly/object-formatters)
- Firefox:
  - [Vue.js devtools](https://addons.mozilla.org/en-US/firefox/addon/vue-js-devtools/)
  - [Turn on Custom Object Formatter in Firefox DevTools](https://fxdx.dev/firefox-devtools-custom-object-formatters/)

## Type Support for `.vue` Imports in TS

TypeScript cannot handle type information for `.vue` imports by default, so we replace the `tsc` CLI with `vue-tsc` for type checking. In editors, we need [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) to make the TypeScript language service aware of `.vue` types.

## Customize configuration

See [Vite Configuration Reference](https://vite.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Type-Check, Compile and Minify for Production

```sh
npm run build
```

## Supabase Setup

1. Create a new project in [Supabase](https://supabase.com)
2. Go to Settings > API and copy your project URL and anon key
3. Create a `.env` file in the root directory with:
   ```
   VITE_SUPABASE_URL=your_project_url
   VITE_SUPABASE_ANON_KEY=your_anon_key
   ```

### Database Schema

Run the following SQL in your Supabase SQL Editor to set up the database:

```sql
-- Create markers table (admin-only creation)
CREATE TABLE IF NOT EXISTS markers (
  id SERIAL PRIMARY KEY,
  lat DOUBLE PRECISION NOT NULL,
  lng DOUBLE PRECISION NOT NULL,
  popup TEXT,
  type INTEGER DEFAULT 1,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create images table
CREATE TABLE images (
  id SERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  title TEXT,
  description TEXT,
  image_url TEXT NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create comments table
CREATE TABLE comments (
  id SERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  image_id INTEGER REFERENCES images(id) ON DELETE CASCADE,
  content TEXT NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create likes table
CREATE TABLE likes (
  id SERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  image_id INTEGER REFERENCES images(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(user_id, image_id)
);

-- Enable RLS on all tables
ALTER TABLE markers ENABLE ROW LEVEL SECURITY;
ALTER TABLE images ENABLE ROW LEVEL SECURITY;
ALTER TABLE comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE likes ENABLE ROW LEVEL SECURITY;

-- RLS Policies
-- Markers: everyone can view, only admins can create/edit/delete
CREATE POLICY "Everyone can view markers" ON markers FOR SELECT USING (true);
CREATE POLICY "Admins can insert markers" ON markers FOR INSERT WITH CHECK (
  EXISTS (
    SELECT 1 FROM auth.users 
    WHERE id = auth.uid() 
    AND raw_user_meta_data->>'role' = 'admin'
  )
);
CREATE POLICY "Admins can update markers" ON markers FOR UPDATE USING (
  EXISTS (
    SELECT 1 FROM auth.users 
    WHERE id = auth.uid() 
    AND raw_user_meta_data->>'role' = 'admin'
  )
);
CREATE POLICY "Admins can delete markers" ON markers FOR DELETE USING (
  EXISTS (
    SELECT 1 FROM auth.users 
    WHERE id = auth.uid() 
    AND raw_user_meta_data->>'role' = 'admin'
  )
);

-- Images: users can view all images, but only edit their own
CREATE POLICY "Users can view all images" ON images FOR SELECT USING (true);
CREATE POLICY "Users can insert own images" ON images FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can update own images" ON images FOR UPDATE USING (auth.uid() = user_id);
CREATE POLICY "Users can delete own images" ON images FOR DELETE USING (auth.uid() = user_id);

-- Comments: users can view all comments, but only edit their own
CREATE POLICY "Users can view all comments" ON comments FOR SELECT USING (true);
CREATE POLICY "Users can insert own comments" ON comments FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can update own comments" ON comments FOR UPDATE USING (auth.uid() = user_id);
CREATE POLICY "Users can delete own comments" ON comments FOR DELETE USING (auth.uid() = user_id);

-- Likes: users can view all likes, but only manage their own
CREATE POLICY "Users can view all likes" ON likes FOR SELECT USING (true);
CREATE POLICY "Users can insert own likes" ON likes FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can delete own likes" ON likes FOR DELETE USING (auth.uid() = user_id);

-- Photo Posts Schema (for Explore page)
-- Create photo_posts table
CREATE TABLE IF NOT EXISTS photo_posts (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  title TEXT,
  description TEXT,
  image_url TEXT NOT NULL,
  marker_id INTEGER REFERENCES markers(id),
  lat DOUBLE PRECISION,
  lng DOUBLE PRECISION,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create photo_post_likes table
CREATE TABLE IF NOT EXISTS photo_post_likes (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  post_id BIGINT REFERENCES photo_posts(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(user_id, post_id)
);

-- Create photo_post_bookmarks table
CREATE TABLE IF NOT EXISTS photo_post_bookmarks (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  post_id BIGINT REFERENCES photo_posts(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(user_id, post_id)
);

-- Enable RLS for photo posts
ALTER TABLE photo_posts ENABLE ROW LEVEL SECURITY;
ALTER TABLE photo_post_likes ENABLE ROW LEVEL SECURITY;
ALTER TABLE photo_post_bookmarks ENABLE ROW LEVEL SECURITY;

-- RLS Policies for photo_posts
CREATE POLICY "Everyone can view photo posts" ON photo_posts FOR SELECT USING (true);
CREATE POLICY "Authenticated users can insert photo posts" ON photo_posts FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can update own photo posts" ON photo_posts FOR UPDATE USING (auth.uid() = user_id);
CREATE POLICY "Users can delete own photo posts" ON photo_posts FOR DELETE USING (auth.uid() = user_id);

-- RLS Policies for photo_post_likes
CREATE POLICY "Everyone can view photo post likes" ON photo_post_likes FOR SELECT USING (true);
CREATE POLICY "Authenticated users can insert own likes" ON photo_post_likes FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can delete own likes" ON photo_post_likes FOR DELETE USING (auth.uid() = user_id);

-- RLS Policies for photo_post_bookmarks
CREATE POLICY "Users can view own bookmarks" ON photo_post_bookmarks FOR SELECT USING (auth.uid() = user_id);
CREATE POLICY "Authenticated users can insert own bookmarks" ON photo_post_bookmarks FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can delete own bookmarks" ON photo_post_bookmarks FOR DELETE USING (auth.uid() = user_id);

-- Photo Dates Schema (for scheduling photo shoots)
-- Create photo_dates table
CREATE TABLE IF NOT EXISTS photo_dates (
  id BIGSERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  start_time TIMESTAMP WITH TIME ZONE NOT NULL,
  end_time TIMESTAMP WITH TIME ZONE NOT NULL,
  capacity INTEGER,
  marker_id INTEGER REFERENCES markers(id),
  marker_name TEXT,
  lat DOUBLE PRECISION,
  lng DOUBLE PRECISION,
  created_by UUID REFERENCES auth.users(id) NOT NULL,
  created_by_username TEXT,
  attendees JSONB DEFAULT '[]'::jsonb,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS for photo_dates
ALTER TABLE photo_dates ENABLE ROW LEVEL SECURITY;

-- RLS Policies for photo_dates
CREATE POLICY "Everyone can view photo dates" ON photo_dates FOR SELECT USING (true);
CREATE POLICY "Authenticated users can insert photo dates" ON photo_dates FOR INSERT WITH CHECK (auth.uid() = created_by);
CREATE POLICY "Users can update own photo dates" ON photo_dates FOR UPDATE USING (auth.uid() = created_by);
CREATE POLICY "Users can delete own photo dates" ON photo_dates FOR DELETE USING (auth.uid() = created_by);
```

### Making a User Admin

To make a user an admin, update their user metadata:

```sql
UPDATE auth.users SET raw_user_meta_data = raw_user_meta_data || '{"role": "admin"}' WHERE email = 'admin@example.com';
```

Or in the app, use the admin panel in My Page (simplified for POC).
