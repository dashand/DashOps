--
-- PostgreSQL database dump
--

\restrict uYwTTcVmsycdI7OfNSpGgZm1WEMFUil2XXFCy4R5AEhNHXdlkMA2TNyjmXKcLU6

-- Dumped from database version 17.6 (Debian 17.6-0+deb13u1)
-- Dumped by pg_dump version 17.6 (Debian 17.6-0+deb13u1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.history DROP CONSTRAINT IF EXISTS history_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.history DROP CONSTRAINT IF EXISTS history_task_id_fkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_username_key;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY public.tasks DROP CONSTRAINT IF EXISTS tasks_pkey;
ALTER TABLE IF EXISTS ONLY public.task_families DROP CONSTRAINT IF EXISTS task_families_pkey;
ALTER TABLE IF EXISTS ONLY public.task_families DROP CONSTRAINT IF EXISTS task_families_name_key;
ALTER TABLE IF EXISTS ONLY public.history DROP CONSTRAINT IF EXISTS history_pkey;
ALTER TABLE IF EXISTS public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tasks ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.task_families ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.history ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.users_id_seq;
DROP TABLE IF EXISTS public.users;
DROP SEQUENCE IF EXISTS public.tasks_id_seq;
DROP TABLE IF EXISTS public.tasks;
DROP SEQUENCE IF EXISTS public.task_families_id_seq;
DROP TABLE IF EXISTS public.task_families;
DROP SEQUENCE IF EXISTS public.history_id_seq;
DROP TABLE IF EXISTS public.history;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: history; Type: TABLE; Schema: public; Owner: ielo_user
--

CREATE TABLE public.history (
    id integer NOT NULL,
    task_id integer,
    user_id integer,
    action character varying(255),
    details text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.history OWNER TO ielo_user;

--
-- Name: history_id_seq; Type: SEQUENCE; Schema: public; Owner: ielo_user
--

CREATE SEQUENCE public.history_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.history_id_seq OWNER TO ielo_user;

--
-- Name: history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ielo_user
--

ALTER SEQUENCE public.history_id_seq OWNED BY public.history.id;


--
-- Name: task_families; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_families (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    display_order integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.task_families OWNER TO postgres;

--
-- Name: task_families_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.task_families_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.task_families_id_seq OWNER TO postgres;

--
-- Name: task_families_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.task_families_id_seq OWNED BY public.task_families.id;


--
-- Name: tasks; Type: TABLE; Schema: public; Owner: ielo_user
--

CREATE TABLE public.tasks (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    status character varying(20) DEFAULT 'todo'::character varying,
    team character varying(50),
    assigned_to character varying(100),
    external_link character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    family character varying(50)
);


ALTER TABLE public.tasks OWNER TO ielo_user;

--
-- Name: tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: ielo_user
--

CREATE SEQUENCE public.tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tasks_id_seq OWNER TO ielo_user;

--
-- Name: tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ielo_user
--

ALTER SEQUENCE public.tasks_id_seq OWNED BY public.tasks.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: ielo_user
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role character varying(20) DEFAULT 'user'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    refresh_rate integer DEFAULT 10,
    auth_source character varying(10) DEFAULT 'local'::character varying
);


ALTER TABLE public.users OWNER TO ielo_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: ielo_user
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO ielo_user;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ielo_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: history id; Type: DEFAULT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.history ALTER COLUMN id SET DEFAULT nextval('public.history_id_seq'::regclass);


--
-- Name: task_families id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_families ALTER COLUMN id SET DEFAULT nextval('public.task_families_id_seq'::regclass);


--
-- Name: tasks id; Type: DEFAULT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.tasks ALTER COLUMN id SET DEFAULT nextval('public.tasks_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: history; Type: TABLE DATA; Schema: public; Owner: ielo_user
--

COPY public.history (id, task_id, user_id, action, details, created_at) FROM stdin;
3	2	1	create	Tâche créée	2025-12-02 20:45:31.254305
4	2	1	update	Statut changé de todo à in_progress	2025-12-02 20:45:55.987459
5	2	1	update	Statut changé de in_progress à done	2025-12-02 20:45:58.988755
6	2	1	update	Statut changé de termine à inconnu	2025-12-02 20:54:54.575933
7	2	1	update	Statut changé de inconnu à en_cours	2025-12-02 20:54:58.983427
8	3	1	create	Tâche créée	2025-12-02 20:55:09.80776
9	3	1	update	Statut changé de en_cours à termine	2025-12-02 20:55:13.396903
10	4	1	create	Tâche créée	2025-12-02 20:55:24.123229
11	5	1	create	Tâche créée	2025-12-03 15:08:17.018066
12	3	1	update	Statut changé de termine à en_cours	2025-12-03 15:09:14.17552
13	3	1	update	Statut changé de en_cours à termine	2025-12-03 18:29:32.815416
14	2	1	update	Statut changé de en_cours à bloque	2025-12-03 18:29:42.111344
15	3	1	update	Statut changé de termine à en_cours	2025-12-03 18:29:47.242439
16	3	1	update	Statut changé de en_cours à termine	2025-12-03 18:29:52.066816
17	6	1	create	Tâche créée	2025-12-03 18:38:51.629417
18	6	1	update	Statut changé de en_cours à inconnu	2025-12-03 18:38:54.478465
19	5	1	update	Statut changé de en_cours à termine	2025-12-03 18:51:52.231261
20	5	1	update	Statut changé de termine à inconnu	2025-12-03 18:51:54.717447
21	5	1	update	Statut changé de inconnu à bloque	2025-12-03 18:51:57.294689
22	5	1	update	Statut changé de bloque à en_cours	2025-12-03 18:51:59.750624
23	5	1	update	Statut changé de en_cours à bloque	2025-12-04 18:45:12.806473
24	7	1	create	Tâche créée	2025-12-04 20:14:51.333916
25	4	1	update	Statut changé de en_cours à inconnu	2025-12-05 19:04:00.977416
26	4	1	update	Statut changé de inconnu à termine	2025-12-05 19:05:01.217364
27	5	1	update	Statut changé de bloque à termine	2025-12-05 21:00:25.784888
28	6	1	update	Titre changé	2025-12-07 11:09:16.734357
29	6	1	update	Description modifiée	2025-12-07 11:09:16.735287
30	6	1	update	Statut changé de inconnu à termine	2025-12-07 11:09:16.735836
31	6	1	update	Famille changée de Infra à WDM	2025-12-07 11:09:16.736332
32	6	1	update	Assignation changée de Personne à change assignation	2025-12-07 11:09:16.736929
33	6	1	update	Lien externe modifié	2025-12-07 11:09:16.737508
34	4	1	update	Statut changé de termine à en_cours	2025-12-07 11:15:05.407655
35	7	1	update	Statut changé de en_cours à inconnu	2025-12-07 11:18:14.429856
36	7	1	update	Famille changée de Infra à IG	2025-12-07 11:18:14.431299
37	7	1	update	<ul class='mb-0 ps-3'><li><b>Titre</b> modifié</li></ul>	2025-12-07 12:16:29.0736
38	5	1	update	<ul class='mb-0 ps-3'><li><b>Statut</b> : termine &rarr; inconnu</li></ul>	2025-12-08 06:52:00.469715
39	5	1	update	<ul class='mb-0 ps-3'><li><b>Famille</b> : WDM &rarr; Infra</li></ul>	2025-12-08 06:52:09.30771
40	8	10	create	Tâche créée	2025-12-08 08:20:12.76619
41	8	10	update	<ul class='mb-0 ps-3'><li><b>Statut</b> : en_cours &rarr; bloque</li></ul>	2025-12-08 08:20:25.240947
42	8	10	update	<ul class='mb-0 ps-3'><li><b>Statut</b> : bloque &rarr; en_cours</li></ul>	2025-12-08 08:20:32.472684
43	9	1	create	Tâche créée	2025-12-08 13:16:35.815185
44	9	1	update	<ul class='mb-0 ps-3'><li><b>Statut</b> : en_cours &rarr; termine</li></ul>	2025-12-08 13:16:53.472606
45	10	12	create	Tâche créée	2025-12-08 14:11:09.600971
46	5	1	update	<ul class='mb-0 ps-3'><li><b>Statut</b> : inconnu &rarr; bloque</li></ul>	2025-12-08 21:59:20.202446
47	8	1	update	<ul class='mb-0 ps-3'><li><b>Description</b> modifiée</li></ul>	2025-12-08 21:59:38.061179
48	9	1	update	<ul class='mb-0 ps-3'><li><b>Description</b> modifiée</li><li><b>Assignation</b> : Captain Ovious &rarr; Captain Obvious</li><li><b>Lien externe</b> modifié</li></ul>	2025-12-08 22:08:49.384581
49	11	11	create	Tâche créée	2025-12-09 13:32:05.316593
50	12	1	create	Tâche créée	2025-12-09 20:31:09.720159
\.


--
-- Data for Name: task_families; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_families (id, name, display_order, created_at) FROM stdin;
1	IG	1	2025-12-08 22:11:58.863314
2	WDM	2	2025-12-08 22:11:58.863314
3	Infra	3	2025-12-08 22:11:58.863314
4	Hardware	4	2025-12-08 22:11:58.863314
5	Transit	5	2025-12-08 22:11:58.863314
6	System	6	2025-12-08 22:11:58.863314
\.


--
-- Data for Name: tasks; Type: TABLE DATA; Schema: public; Owner: ielo_user
--

COPY public.tasks (id, title, description, status, team, assigned_to, external_link, created_at, updated_at, family) FROM stdin;
9	Merci viaveis	Ajout description	termine	\N	Captain Obvious	www.42.fr	2025-12-08 13:16:35.812319	2025-12-08 22:08:49.376654	IG
11	Remise en route topolograph	Topolograph ne loggue plus rien sur les dashboards grafana. ⛓️‍💥	en_cours	\N	seb	https://topolograph.int.as29075.net/	2025-12-09 13:32:05.309761	2025-12-09 13:32:05.309761	System
12	test assignation		en_cours	\N			2025-12-09 20:31:09.717331	2025-12-09 20:31:09.717331	System
3	test		termine	\N	Tommy	www.url.com	2025-12-02 20:55:09.805357	2025-12-03 18:30:04.385325	IG
2	test 34	fdsjkl\r\nAjout description	bloque	ingenierie	Sebastien terst	url.bulshit	2025-12-02 20:45:31.246182	2025-12-05 19:04:40.491401	WDM
6	task inconnuTask Inconnu - Titre Modifié 2	change description	termine	\N	change assignation	change url	2025-12-03 18:38:51.623439	2025-12-07 11:09:16.732339	WDM
4	3		en_cours	\N			2025-12-02 20:55:24.121346	2025-12-07 11:15:05.404304	WDM
7	test autorefresh 1316	testA	inconnu	\N	sla lala		2025-12-04 20:14:51.329929	2025-12-07 12:16:29.071098	IG
10	Clean EquinixIX		en_cours	\N	MP		2025-12-08 14:11:09.597217	2025-12-08 14:11:09.597217	Transit
5	Intégration ISIS OLT de Lorient (LCT56)Intégration ISIS OLT Lorient (LCT56) - Modifié	Intégration de l'OLT dans ISIS 	bloque	\N	dédé	http://lien-externe.ielo.net	2025-12-03 15:08:17.012538	2025-12-08 21:59:20.199556	Infra
8	C'est cassé chef !	On tente de fix l'histo	en_cours	\N			2025-12-08 08:20:12.760545	2025-12-08 21:59:38.058941	WDM
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: ielo_user
--

COPY public.users (id, username, password_hash, role, created_at, refresh_rate, auth_source) FROM stdin;
1	admin	$2y$12$.MfjQDY6NNbqJkOnCwUPseOBh/erQXMbiHL4mQBhDqOM3Z6pI3w9u	admin	2025-12-02 20:28:46.96973	10	local
2	mgomez	$2y$12$nrKZzXUfFoTccRfnDFcAA.uRxrrjjmkX7fKGU2mAmXVrTlPi8PDe2	user	2025-12-03 19:20:49.168069	10	local
5	sghilas	$2y$12$wyJhXbeH3nfAw6o6/C//F.DHiQOg5/kiDJ.belxclIepv1tLGZ6Jm	user	2025-12-03 19:22:15.059015	10	local
6	bcg	$2y$12$/f.3c028U4noBtl1nhcGY.SMxlX/fbv0c0/bHGMtYUk638hhZ0pY.	user	2025-12-03 19:22:27.653351	10	local
8	tbuisson	$2y$12$q0LTj6UqXqzzcN7YYNu41uHDx6lXUbKwOnk2pKFZ.l9kU/Gv3N4Ei	user	2025-12-04 08:19:35.089531	10	local
9	moniteur1	$2y$12$Lqa12xA1N/J0hgVx5SoQt.J8G0s5CWJSK/9Gn8xKuwWTyMCcn65qG	moniteur	2025-12-07 11:52:19.999744	10	local
10	rsi	$2y$12$2uCaY8LzlX7Wwky/7h2EKehOKKH6BckJuBTkprMZPBZdur3AEUGka	user	2025-12-08 08:19:01.20188	10	local
3	aluquin	$2y$12$paJmfxkZORxNjsALjK01GO.H/vxQxSFs9MCMQxmFVqrptXZ5149WC	admin	2025-12-03 19:20:59.273521	10	local
7	hdc	$2y$12$yWDuPClnvs7O74duDB7ISOnmRgTfk5tkvAkIeAt8PaMJRMIDrKR7u	admin	2025-12-03 19:22:36.555356	10	local
12	mp	$2y$12$obpr.QwwJC6.Z8/IxQfM3ushPsao2K9HZGvA495ZLLrzlMoGDNQNS	user	2025-12-08 10:25:30.490251	10	local
13	cp	$2y$12$iKanLi0msE1udtq3gk1XTuk5oeT.Nsi6C4PJqJfppEWYFflvjn332	user	2025-12-08 10:26:08.500011	10	local
14	emo	$2y$12$QNWYiVFHzWGlknjzdMzxwONELPoPPfu8aoBUjEvgGHu1z6cDJzucu	user	2025-12-08 10:26:24.433666	10	local
15	tb	$2y$12$yoX42KXsEd1bKxWyCeyUvOuYBsXaL9hgjb5RWG8JWP.Y4rHR5Y.Gm	user	2025-12-08 10:26:45.112511	10	local
16	jv	$2y$12$Q0gbtOgPjGn.3wo6GN0oB.AklKT.xSe4bW/A9WKeU36jiSCnmKBVG	user	2025-12-08 10:27:01.08864	10	local
17	jm	$2y$12$K309pGqysUFkg25MmWnHfOkqinwFjIvxa4TpR29HjPoiapbUmePQ.	user	2025-12-08 10:27:15.853675	10	local
4	slalanne	$2y$12$x0h2xxOMQTd4x27.FiR6bORcpr9R45VF5GtSdqBUnnY0QttiEd3Ee	user	2025-12-03 19:21:08.63409	10	local
19	db	$2y$12$zouH.koaj/Z..qnvbUa0lO/BwdF.t./3dmuUjRHxCKnxgLXxuzvMy	user	2025-12-08 15:37:31.562826	10	local
18	jb	$2y$12$j7KNQ6ueKCYS28Ju1BXLg.EHcjycUBY4ANtZqKcJDQkHO88N31Oje	user	2025-12-08 10:27:51.166765	10	local
11	seb	$2y$12$IXKuDbdb4ThvFh53UyiOLu.7L45uTHwQWn.WrTEuAvY1Qw7P8kstO	user	2025-12-08 10:24:39.934315	10	local
\.


--
-- Name: history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ielo_user
--

SELECT pg_catalog.setval('public.history_id_seq', 50, true);


--
-- Name: task_families_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.task_families_id_seq', 6, true);


--
-- Name: tasks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ielo_user
--

SELECT pg_catalog.setval('public.tasks_id_seq', 12, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ielo_user
--

SELECT pg_catalog.setval('public.users_id_seq', 19, true);


--
-- Name: history history_pkey; Type: CONSTRAINT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.history
    ADD CONSTRAINT history_pkey PRIMARY KEY (id);


--
-- Name: task_families task_families_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_families
    ADD CONSTRAINT task_families_name_key UNIQUE (name);


--
-- Name: task_families task_families_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_families
    ADD CONSTRAINT task_families_pkey PRIMARY KEY (id);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: history history_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.history
    ADD CONSTRAINT history_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: history history_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ielo_user
--

ALTER TABLE ONLY public.history
    ADD CONSTRAINT history_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict uYwTTcVmsycdI7OfNSpGgZm1WEMFUil2XXFCy4R5AEhNHXdlkMA2TNyjmXKcLU6

